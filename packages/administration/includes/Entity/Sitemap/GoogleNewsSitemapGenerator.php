<?php

/** @noinspection XmlUnusedNamespaceDeclaration */

declare(strict_types=1);

namespace Scm\Entity\Sitemap;

use DateTime;
use SimpleXMLElement;

/**
 * Class GoogleNewsSitemapGenerator
 *
 * @package Scm\entity
 *
 * @link https://support.google.com/news/publisher-center/answer/9606710?hl=en&ref_topic=9606468
 */
class GoogleNewsSitemapGenerator
{
    private string $siteUrl;

    private string $outputDir;

    private string $publication;

    private string $language;

    private int $maxUrls = 50000;

    private string $indexFileName = 'sitemap-news-index.xml';

    private string $filePrefix = 'sitemap-news';

    private string $fileExtension = 'xml';

    private array $entries = [];

    private array $outputFiles = [];

    /**
     * GoogleNewsSitemapGenerator constructor.
     *
     * @param string $siteUrl
     * @param string $outputDir
     * @param string $publication
     * @param string $language
     */
    public function __construct(
        string $siteUrl,
        string $outputDir,
        string $publication,
        string $language = 'en'
    ) {
        $this->siteUrl = $siteUrl;
        $this->outputDir = $outputDir;
        $this->publication = $publication;
        $this->language = $language;
    }

    /**
     * Set max number of URLs per sitemap file.
     *
     * @param int $value
     *
     * @return $this
     */
    public function setMaxUrlsPerSitemap(int $value): self
    {
        $this->maxUrls = $value;

        return $this;
    }

    /**
     * Set sitemap index file name.
     *
     * @param string $fileName
     *
     * @return $this
     */
    public function setSitemapIndexFileName(string $fileName): self
    {
        $this->indexFileName = $fileName;

        return $this;
    }

    /**
     * Set sitemap base file name.
     *
     * @param string $fileName
     *
     * @return $this
     */
    public function setSitemapFileName(string $fileName): self
    {
        $pathInfo = pathinfo($fileName);
        $this->filePrefix = $pathInfo['filename'];
        $this->fileExtension = $pathInfo['extension'];

        return $this;
    }


    /**
     * @return array
     */
    public function getOutputFiles(): array
    {
        return $this->outputFiles;
    }

    /**
     * @return string
     */
    public function getIndexFileName(): string
    {
        return $this->indexFileName;
    }

    /**
     * Add URL to the sitemap.
     *
     * @param string $path
     * @param \DateTime $dateTime
     * @param string $title
     */
    public function addURL(string $path, DateTime $dateTime, string $title): void
    {
        $this->entries[] = [
            'url' => $this->siteUrl . $path,
            'date' => $dateTime->format('Y-m-d'),
            'title' => $title,
        ];
    }

    /**
     * Write current list of URLs from memory into temporary files.
     */
    public function flush(): void
    {
        $offset = 0;
        $length = count($this->entries);
        do {
            $entries = array_slice($this->entries, $offset * $this->maxUrls, $this->maxUrls);
            $sitemapFile = tempnam($this->outputDir, 'sitemap-news-gen--');
            $this->writeEntriesToFile($entries, $sitemapFile);
            $this->outputFiles[] = $sitemapFile;
            $offset += 1;
        } while ($length > $offset * $this->maxUrls);
        $this->entries = [];
    }

    /**
     * Write sitemap entries to a file.
     *
     * @param array $entries
     * @param string $filePath
     */
    private function writeEntriesToFile(array $entries, string $filePath): void
    {
        $xml = new SimpleXMLElement(
        /** @lang XML */
        // phpcs:disable
            <<<'SITEMAPINDEX'
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:news="http://www.google.com/schemas/sitemap-news/0.9"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:schemaLocation="http://www.google.com/schemas/sitemap-news/0.9 http://www.google.com/schemas/sitemap-news/0.9/sitemap-news.xsd http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">
</urlset>
SITEMAPINDEX
        );
        // phpcs:enable
        foreach ($entries as $entry) {
            $url = $xml->addChild('url');
            $url->addChild('loc', $entry['url']);
            $news = $url->addChild('news:news', null, 'news');
            $publication = $news->addChild('publication');
            $publication->addChild('name', $this->publication);
            $publication->addChild('language', $this->language);
            $news->addChild('publication_date', $entry['date']);
            $news->addChild('title', htmlspecialchars($entry['title']));
        }
        file_put_contents($filePath, str_replace(' xmlns:news="news"', '', $xml->asXML()));
    }

    /**
     * Write sitemaps and sitemap index to the final destination.
     */
    public function finalize(): void
    {
        $index = 1;
        $this->outputFiles = array_map(function (string $filePath) use (&$index): string {
            $newFileName = "$this->filePrefix$index.$this->fileExtension";
            $index += 1;
            $newFilePath = "$this->outputDir/$newFileName";

            return rename($filePath, $newFilePath) ? $newFilePath : $filePath;
        }, $this->outputFiles);

        $this->writeIndexFile();
    }

    /**
     * Write sitemap index file.
     */
    private function writeIndexFile(): void
    {
        $xml = new SimpleXMLElement(
        // phpcs:disable
        /** @lang XML */
            <<<'SITEMAPINDEX'
<?xml version="1.0" encoding="UTF-8"?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
              xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 https://www.sitemaps.org/schemas/sitemap/0.9/siteindex.xsd">
</sitemapindex>
SITEMAPINDEX
        );
        // phpcs:enable
        $now = (new DateTime())->format('c');
        foreach ($this->outputFiles as $file) {
            $sitemap = $xml->addChild('sitemap');
            $sitemap->addChild('loc', $this->siteUrl . '/' . pathinfo($file, PATHINFO_BASENAME));
            $sitemap->addChild('lastmod', $now);
        }
        $xml->asXML("$this->outputDir/$this->indexFileName");
    }
}
