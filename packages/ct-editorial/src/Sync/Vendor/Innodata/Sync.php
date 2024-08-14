<?php

declare(strict_types=1);

namespace Cra\CtEditorial\Sync\Vendor\Innodata;

use Cra\CtEditorial\Sync\Vendor\SyncInterface;
use Exception;
use phpseclib3\Net\SFTP;
use Scm\Tools\Logger;
use ZipArchive;

use function get_temp_dir;
use function untrailingslashit;

/**
 * Class Sync for importing Innodata feed items stored on SFTP server in ZIP files.
 */
final class Sync implements SyncInterface
{
    private Parser $parser;

    private SFTP $sftp;

    /**
     * @var string Remote SFTP root directory without trailing slash.
     */
    private string $remoteRootDir;

    /**
     * @var string Innodata subdir without slashes.
     */
    private string $remoteSubDir = 'innodata';

    /**
     * @var string Innodata archived subdir without slashes.
     */
    private string $remoteSubDirArchived = 'innodata-archived';

    /**
     * @var string Innodata failed subdir without slashes.
     */
    private string $remoteSubDirFailed = 'innodata-failed';

    /**
     * @var string Temporary directory without trailing slash.
     */
    private string $tmpDir;

    /**
     * @var string[]
     */
    private array $zipFiles;

    /**
     * @var string[]
     */
    private array $skippedRemoteFiles;

    /**
     * @var string[]
     */
    private array $xmlFiles;

    /**
     * @var string[]
     */
    private array $failedToImportXmlFiles;

    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->parser = new Parser();
    }

    /**
     * Connect to the SFTP host and locate temporary directory to work in.
     *
     * @param array{host: string, port: ?int, username: string, password: string} $config
     *
     * @inheritDoc
     */
    public function setup(array $config = []): void
    {
        $config += ['port' => 22];

        // Connect to the remote server.
        $this->sftp = new SFTP($config['host'], $config['port']);
        $isSuccess = $this->sftp->login($config['username'], $config['password']);
        if (!$isSuccess) {
            throw new Exception(sprintf('Unable to connect to %s!', $config['host']));
        }

        // Set and check the necessary remote directory exists.
        $this->remoteRootDir = untrailingslashit($this->sftp->pwd());
        if (!$this->sftp->file_exists("$this->remoteRootDir/$this->remoteSubDir")) {
            throw new Exception(
                "$this->remoteRootDir/$this->remoteSubDir directory is missing on the remote server!"
            );
        }

        $this->tmpDir = untrailingslashit(get_temp_dir());
    }

    /**
     * @inheritDoc
     */
    public function execute(): void
    {
        $this->findFilesToImport();
        foreach ($this->xmlFiles as $xmlFile) {
            try {
                $this->importSingleFile($xmlFile);
            } catch (Exception $exception) {
                $this->failedToImportXmlFiles[] = $xmlFile;
                Logger::log($exception->getMessage(), 'warning');
            }
        }
        $this->archiveImportedFiles();
        $this->cleanup();
    }

    /**
     * Find Innodata feed item files to import.
     */
    private function findFilesToImport(): void
    {
        $this->zipFiles = [];
        $this->skippedRemoteFiles = [];
        $this->xmlFiles = [];
        $this->failedToImportXmlFiles = [];

        $this->findRemoteFilesToImport();
        $this->unzipFiles();
    }

    /**
     * Find ZIP files to import on the remote server.
     */
    private function findRemoteFilesToImport(): void
    {
        $files = $this->sftp->nlist("$this->remoteRootDir/$this->remoteSubDir", true);
        foreach ($files as $file) {
            $remoteFilePath = "$this->remoteRootDir/$this->remoteSubDir/$file";
            $extension = pathinfo($file, PATHINFO_EXTENSION);
            if ($extension === 'zip') {
                $this->zipFiles[] = $remoteFilePath;
            } elseif ($extension === 'xml') {
                $localFile = "$this->tmpDir/$this->remoteSubDir/$file";
                $localFileDir = pathinfo($localFile, PATHINFO_DIRNAME);
                if (!file_exists($localFileDir) && !mkdir($localFileDir, 0777, true)) {
                    $this->skippedRemoteFiles[] = $remoteFilePath;
                    continue;
                }
                if (!$this->sftp->get($remoteFilePath, $localFile)) {
                    $this->skippedRemoteFiles[] = $remoteFilePath;
                    continue;
                }
                $this->xmlFiles[] = $localFile;
            }
        }
    }

    /**
     * Unzip files into XML files.
     */
    private function unzipFiles(): void
    {
        foreach ($this->zipFiles as $zipFile) {
            try {
                Logger::log("Extracting ZIP file $zipFile...", 'info');
                $numberExtractedXmlFiles = $this->unzipFile($zipFile);
                Logger::log("Found $numberExtractedXmlFiles XML file(s).", 'info');
            } catch (Exception $exception) {
                Logger::log($exception->getMessage(), 'warning');
                Logger::log("Skipping ZIP file $zipFile.", 'info');
                $this->skippedRemoteFiles[] = $zipFile;
            }
        }
        $this->zipFiles = array_values(array_diff($this->zipFiles, $this->skippedRemoteFiles));
    }

    /**
     * Unzip file into XML files.
     *
     * @param string $zipFile
     *
     * @return int Returns number of extracted XML files.
     *
     * @throws Exception
     */
    private function unzipFile(string $zipFile): int
    {
        // Ensure directory exists for the ZIP file.
        $zipFileDir = $this->tmpDir . pathinfo($zipFile, PATHINFO_DIRNAME);
        if (!file_exists($zipFileDir) && !mkdir($zipFileDir, 0777, true)) {
            throw new Exception("Cannot create directory $zipFileDir");
        }

        // Download ZIP file.
        if (!$this->sftp->get($zipFile, $this->tmpDir . $zipFile)) {
            throw new Exception("Unable to download ZIP file: $this->tmpDir$zipFile");
        }

        // Extract ZIP file.
        $zip = new ZipArchive();
        if ($zip->open($this->tmpDir . $zipFile) !== true) {
            throw new Exception("Unable to open ZIP: $this->tmpDir$zipFile");
        }
        if (!$zip->extractTo($zipFileDir)) {
            throw new Exception("Unable to extract files from ZIP: $this->tmpDir$zipFile");
        }
        $zip->close();
        unlink($this->tmpDir . $zipFile);

        // Locate XML files.
        $xmlFiles = glob("$zipFileDir/*.xml");
        $this->xmlFiles = array_merge($this->xmlFiles, $xmlFiles);

        return count($xmlFiles);
    }

    /**
     * Import single feed item file.
     *
     * @param string $filePath
     *
     * @throws Exception
     */
    private function importSingleFile(string $filePath): void
    {
        $xml = file_get_contents($filePath);

        Logger::log("Parsing feed item located at $filePath...", 'info');
        $editorial = $this->parser->parse($xml);
        Logger::log("Feed item unique ID: {$editorial->vendorFields()->uniqueId()}", 'info');

        $isUpdated = $editorial->upsert('vendor');
        $logMessage = $isUpdated ?
            "Editorial {$editorial->title()} has been saved with ID: {$editorial->id()}." :
            "Already exists (ID: {$editorial->id()}). Skipping.";
        Logger::log($logMessage, 'info');
    }

    /**
     * Archive imported files.
     */
    private function archiveImportedFiles(): void
    {
        // Ensure archive directories exist.
        $list = $this->sftp->nlist("$this->remoteRootDir/");
        if (!in_array("$this->remoteSubDirArchived", $list, true)) {
            $this->sftp->mkdir("$this->remoteRootDir/$this->remoteSubDirArchived");
        }
        if (!in_array("$this->remoteSubDirFailed", $list, true)) {
            $this->sftp->mkdir("$this->remoteRootDir/$this->remoteSubDirFailed");
        }

        foreach ($this->zipFiles as $zipFile) {
            try {
                $this->archiveZipFile($zipFile, false);
            } catch (Exception $exception) {
                Logger::log($exception->getMessage(), 'warning');
            }
        }

        foreach ($this->skippedRemoteFiles as $zipFile) {
            try {
                $this->archiveZipFile($zipFile, true);
            } catch (Exception $exception) {
                Logger::log($exception->getMessage(), 'warning');
            }
        }

        foreach ($this->failedToImportXmlFiles as $xmlFile) {
            try {
                $this->archiveFailedXmlFile($xmlFile);
            } catch (Exception $exception) {
                Logger::log($exception->getMessage(), 'warning');
            }
        }
    }

    /**
     * Archive ZIP file.
     *
     * @param string $zipFile
     * @param bool $isSkipped
     *
     * @throws Exception
     */
    private function archiveZipFile(string $zipFile, bool $isSkipped): void
    {
        $newZipFile = str_replace(
            "/$this->remoteSubDir/",
            $isSkipped ? "/$this->remoteSubDirFailed/" : "/$this->remoteSubDirArchived/",
            $zipFile
        );
        Logger::log("Moving $zipFile into $newZipFile on the remote server...", 'info');
        $newZipFileDir = pathinfo($newZipFile, PATHINFO_DIRNAME);
        $this->sftp->mkdir($newZipFileDir, -1, true);
        if (!$this->sftp->rename($zipFile, $newZipFile)) {
            throw new Exception("Unable to move into $newZipFile");
        }
    }

    /**
     * Archive failed to import XML file.
     *
     * @param string $xmlFile
     *
     * @throws Exception
     */
    private function archiveFailedXmlFile(string $xmlFile): void
    {
        $remoteXmlFile = str_replace(
            "$this->tmpDir/$this->remoteSubDir/",
            "/$this->remoteSubDirFailed/",
            $xmlFile
        );
        Logger::log(
            "Putting failed XML $xmlFile into $remoteXmlFile on the remote server...",
            'info'
        );
        $remoteXmlFileDir = pathinfo($remoteXmlFile, PATHINFO_DIRNAME);
        $this->sftp->mkdir($remoteXmlFileDir, -1, true);
        if (!$this->sftp->put($remoteXmlFile, $xmlFile, SFTP::SOURCE_LOCAL_FILE)) {
            throw new Exception("Unable to upload $remoteXmlFile");
        }
    }

    /**
     * Remove all files created in temporary directory.
     */
    private function cleanup(): void
    {
        foreach ($this->xmlFiles as $xmlFile) {
            if (file_exists($xmlFile) && !unlink($xmlFile)) {
                Logger::log("Unable to delete $xmlFile", 'warning');
            }
        }

        $filesToCleanup = $this->sftp->nlist("$this->remoteRootDir/$this->remoteSubDir", true);
        // Remove '..' directories.
        $regex = '/(\.\.$)|(\.\.\/)/m';
        $filesToCleanup = array_filter(
            $filesToCleanup,
            fn(string $path) => $path !== '.' && !preg_match($regex, $path)
        );
        // Make sure that more nested directories and files go first.
        // Also, make sure that files inside a directory go before its directory.
        usort($filesToCleanup, static function (string $left, string $right): int {
            $rightCount = substr_count($right, '/');
            $leftCount = substr_count($left, '/');
            if ($rightCount > $leftCount) {
                return 1;
            }
            if ($rightCount < $leftCount) {
                return -1;
            }

            return strlen($right) <=> strlen($left);
        });
        // Delete processed remote files.
        foreach ($filesToCleanup as $file) {
            $remoteFilePath = "$this->remoteRootDir/$this->remoteSubDir/$file";
            Logger::log("Remote cleanup: deleting '$remoteFilePath' ...", 'info');
            if (!$this->sftp->delete($remoteFilePath)) {
                Logger::log("Unable to delete $remoteFilePath", 'warning');
            }
        }
    }
}
