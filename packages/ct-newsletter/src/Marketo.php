<?php

declare(strict_types=1);

namespace Cra\CtNewsletter;

use Cra\BlemmyaeApplications\BlemmyaeApplications;
use Cra\MarketoApi\Client;
use Cra\MarketoApi\Endpoint\Asset\Email as EmailEndpoint;
use Cra\MarketoApi\Endpoint\Asset\EmailTemplate as EmailTemplateEndpoint;
use Cra\MarketoApi\Endpoint\Asset\Folder as FolderEndpoint;
use Cra\MarketoApi\Endpoint\Asset\Program as ProgramEndpoint;
use Cra\MarketoApi\Entity\Asset\Email;
use Cra\MarketoApi\Entity\Asset\Folder;
use Cra\MarketoApi\Entity\Asset\Program;
use Cra\MarketoApi\Entity\Asset\Text;
use DateTime;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Scm\Tools\Logger;
use Throwable;

/**
 * Marketo's integration class.
 */
class Marketo
{
    private const EMAIL_TEMPLATE_NAME = 'SC Media API';

    private const EMAIL_TEMPLATE_V2_NAME = 'SC Media API v2';

    private string $environment;

    /**
     * Program template fallback.
     * @var string
     */
    protected string $fallbackProgramTemplateCampaign = '';

    /**
     * Newsletter campaign site machine name.
     * @var string
     */
    protected string $site = '';

    /**
     * Newsletter campaign type.
     * @var string
     */
    protected string $type = '';

    private Client $client;

    private FolderEndpoint $folderEndpoint;

    private ProgramEndpoint $programEndpoint;

    private EmailEndpoint $emailEndpoint;

    private EmailTemplateEndpoint $emailTemplateEndpoint;

    /**
     * Class constructor.
     *
     * @param string $environment
     * @param string $type
     */
    public function __construct(string $environment, string $type)
    {
        $this->type = $type;
        $this->site = $this->mapNewsletterTypeToApp($this->type);
        $this->environment = $environment;
        $this->fallbackProgramTemplateCampaign();
    }

    /**
     * Define default program template.
     * @return string
     */
    public function fallbackProgramTemplateCampaign(): string
    {
        if (!$this->fallbackProgramTemplateCampaign) {
            $this->fallbackProgramTemplateCampaign = match ($this->site) {
                BlemmyaeApplications::CE2E, BlemmyaeApplications::MSSP => 'Daily',
                default => 'DailyScan',
            };
        }

        return $this->fallbackProgramTemplateCampaign;
    }

    /**
     * Setup Marketo integration.
     *
     * @param array $config
     *
     * @return self
     *
     * @throws GuzzleException
     * @throws Exception
     */
    public function setup(array $config = []): self
    {
        $config += [
            'restBaseUrl' => MARKETO_REST_BASE_URL,
            'identityBaseUrl' => MARKETO_IDENTITY_BASE_URL,
            'clientId' => MARKETO_CLIENT_ID,
            'clientSecret' => MARKETO_CLIENT_SECRET,
        ];
        $this->client = (new Client($config))->authenticate();
        $this->folderEndpoint = new FolderEndpoint($this->client);
        $this->programEndpoint = new ProgramEndpoint($this->client);
        $this->emailEndpoint = new EmailEndpoint($this->client);
        $this->emailTemplateEndpoint = new EmailTemplateEndpoint($this->client);

        return $this;
    }

    /**
     * Get email template name by newsletter type.
     *
     * @param string $type
     *  Newsletter type.
     *
     * @return string
     *  Template name.
     */
    public function emailTemplateNameByType(string $type): string
    {
        // Map default template by newsletter type. If type is not specified => use default template.
        $templateMapping = [
            'identity' => self::EMAIL_TEMPLATE_V2_NAME,
            'ai' => self::EMAIL_TEMPLATE_V2_NAME,
            'netsec' => self::EMAIL_TEMPLATE_V2_NAME,
            'appsec' => self::EMAIL_TEMPLATE_V2_NAME,
            'cloud' => self::EMAIL_TEMPLATE_V2_NAME,
            'threat' => self::EMAIL_TEMPLATE_V2_NAME,
            'daily scan' => self::EMAIL_TEMPLATE_V2_NAME,
            'ransomware' => self::EMAIL_TEMPLATE_V2_NAME,
            'default' => self::EMAIL_TEMPLATE_NAME,
        ];

        return $templateMapping[strtolower($type)] ?? $templateMapping['default'];
    }

    /**
     * Push newsletter email to Marketo.
     *
     * @param string $subject
     * @param string $body
     * @param DateTime $date
     * @param array $testEmails
     * @throws Exception
     */
    public function push(
        string $subject,
        string $body,
        DateTime $date,
        array $testEmails = []
    ): void {
        // Get email template name.
        $templateName = $this->emailTemplateNameByType($this->type);

        // Load info about campaign, program and assets.
        $marketoCampaignType = $this->mapNewsletterTypeToMarketoCampaignType($this->type);
        $targetFolder = $this->ensureFolderStructure($marketoCampaignType, $date);
        $program = $this->ensureProgramExists($marketoCampaignType, $date, $targetFolder);
        $email = $this->findEmail($program, $targetFolder, $templateName);

        // Update body.
        $this->emailEndpoint->updateEditableSection(
            $email->id(),
            'edit_text_1',
            new Text($body)
        );

        if (!empty($subject)) {
            if ($email->subject() !== $subject) {
                $this->emailEndpoint->updateContent(
                    $email->id(),
                    ['subject' => new Text($subject)]
                );
            }
            if ($email->name() !== $subject) {
                $this->emailEndpoint->update($email->id(), ['name' => $subject]);
            }
        }

        // Send sample
        if ($testEmails) {
            $emailEndpoint = $this->emailEndpoint;
            foreach ($testEmails as $testEmail) {
                try {
                    $emailEndpoint->sendSample($email->id(), ['emailAddress' => $testEmail]);
                } catch (Throwable $error) {
                    Logger::log($error->getMessage(), 'warning');
                }
            }
            return;
        }

        try {
            // Un-approve existing program.
            $this->programEndpoint->unapprove($program->id());
        } catch (Throwable $error) {
            Logger::log($error->getMessage(), 'warning');
        }
        try {
            // Set start date.
            $this->programEndpoint->update($program->id(), ['startDate' => $this->prepareTime()]);
        } catch (Throwable $error) {
            Logger::log('Provided start date - ' . $this->prepareTime(), 'warning');
            Logger::log($error->getMessage(), 'warning');
        }
        // Approve program.
        // @todo: temporary disabled.
        // $approve = $this->programEndpoint->approve($program->id());
        // if (!$approve) {
        //    throw new Exception(
        //        "Unable to send [$marketoCampaignType] $subject over to the marketo: {$email->url()}"
        //    );
        // }
    }

    /**
     * Could be more elegant, but I am lazy to clean up due to API restriction
     * like that ($minutes % 15 === 0) - as restriction as solution.
     *
     * @return string
     * @throws Exception
     */
    private function prepareTime(): string
    {
        $date = new DateTime();
        $date->modify('+15 minutes');
        $hour = (int)$date->format('H');
        $minutes = (int)$date->format('i');
        // Match Api restriction.
        while ($minutes % 15 !== 0) {
            $minutes++;
        }
        if ($minutes >= 45) {
            $hour++;
            $minutes = 0;
        }
        $date->setTime($hour, $minutes);
        return $date->format('c');
    }

    /**
     * Map newsletter type in CMS to its name in Marketo.
     *
     * @param string $newsletterType
     *
     * @return string
     */
    private function mapNewsletterTypeToMarketoCampaignType(string $newsletterType): string
    {
        return match ($newsletterType) {
            'Cloud' => 'CloudSecurity',
            'Threat' => 'ThreatHorizon',
            'MSSP Daily', 'E2E Daily' => 'Daily',
            'MSSP Top', 'E2E Top' => 'Top',
            'MSSP Webcasts', 'E2E Webcasts' => 'Webcasts',
            'E2E BreakingNews' => 'BreakingNews',
            'E2E Sponsor' => 'Sponsor',
            default => str_replace(' ', '', $newsletterType),
        };
    }

    private function mapNewsletterTypeToApp(string $newsletterType): string
    {
        return match ($newsletterType) {
            'MSSP Daily', 'MSSP Top', 'MSSP Webcasts' => BlemmyaeApplications::MSSP,
            'E2E Daily', 'E2E Sponsor', 'E2E Top', 'E2E Webcasts', 'E2E BreakingNews' => BlemmyaeApplications::CE2E,
            default => BlemmyaeApplications::SCM,
        };
    }

    /**
     * Current Sitecode.
     *
     * @return string
     */
    private function templatePrefix(): string
    {
        return match ($this->site) {
            BlemmyaeApplications::SCM => 'SC',
            BlemmyaeApplications::CSC => 'СSC',
            BlemmyaeApplications::CISO => 'CISO',
            BlemmyaeApplications::CE2E => 'E2E',
            BlemmyaeApplications::MSSP => 'MSSP',
            default => '',
        };
    }

    /**
     * Ensure necessary folder structure for the newsletter.
     *
     * @param string $marketoCampaignType
     * @param DateTime $date
     *
     * @return Folder
     * @throws Exception
     */
    private function ensureFolderStructure(string $marketoCampaignType, DateTime $date): Folder
    {
        $orderedFolders = $this->environment === 'prod' ? [] : ['_Dev'];
        $suffix = $this->environment === 'prod' ? '' : ' Dev';
        $templatePrefix = $this->templatePrefix();

        $dateYear = $date->format('Y');
        $dateYearMonth = $date->format('Y-m');

        switch ($this->site) {
            case BlemmyaeApplications::SCM:
                $folders = [
                    "$templatePrefix Media $dateYear$suffix",
                    "$templatePrefix Newsletters $dateYear$suffix",
                    "$templatePrefix $marketoCampaignType $dateYear$suffix",
                    "$templatePrefix $marketoCampaignType $dateYearMonth$suffix"
                ];
                break;
            case BlemmyaeApplications::MSSP:
            case BlemmyaeApplications::CE2E:
                $prefix = 'AN';
                $folders = [
                    "$prefix $dateYear $suffix",
                    "$prefix $templatePrefix $dateYear $suffix",
                    "$prefix $templatePrefix $marketoCampaignType $dateYear $suffix",
                    "$prefix $templatePrefix $marketoCampaignType $dateYearMonth $suffix"
                ];
                break;
            default:
                $folders = [];
        }

        $orderedFolders = array_merge($orderedFolders, $folders);

        return $this->createFolders($orderedFolders);
    }

    /**
     * Create folders in order of array and return the last one.
     *
     * @param array $folderNames
     *
     * @return Folder
     * @throws Exception
     */
    private function createFolders(array $folderNames): Folder
    {
        if (empty($folderNames)) {
            throw new Exception('List of folder names cannot be empty!');
        }

        $folderName = array_shift($folderNames);
        $folder = $this->folderEndpoint->queryByName($folderName);
        if (!$folder) {
            throw new Exception("Cannot find required parent folder: $folderName");
        }
        foreach ($folderNames as $folderName) {
            $folder = $this->ensureFolderExists($folder, $folderName);
        }

        return $folder;
    }

    /**
     * Ensure Assets folder exists in program and return it.
     *
     * @param Folder $rootFolder
     * @param string $folderName
     *
     * @return Folder
     * @throws Exception
     */
    private function ensureFolderExists(Folder $rootFolder, string $folderName): Folder
    {
        $folder = $this->folderEndpoint->queryByName(
            $folderName,
            ['root' => $rootFolder->folderId()]
        );
        if (!$folder) {
            $folder = $this->folderEndpoint->create(
                $folderName,
                $rootFolder->folderId(),
                'Automatically created folder.'
            );
        }

        return $folder;
    }

    /**
     * Find the program or clone a new one from the appropriate template.
     *
     * @param string $marketoCampaignType
     * @param DateTime $date
     * @param Folder $folder
     *
     * @return Program
     * @throws Exception
     */
    private function ensureProgramExists(
        string $marketoCampaignType,
        DateTime $date,
        Folder $folder
    ): Program {
        $programTemplate = $this->findProgramTemplate($marketoCampaignType);
        $program = $this->programEndpoint->queryByName($this->programName(
            $marketoCampaignType,
            $date
        ));
        if (!$program) {
            $program = $this->programEndpoint->clone(
                $programTemplate->id(),
                $folder->folderId(),
                $this->programName($marketoCampaignType, $date),
                'Automatically created campaign.'
            );
        }

        return $program;
    }

    /**
     * Find program template to clone from.
     *
     * @param string $marketoCampaignType
     *
     * @return Program
     * @throws Exception
     */
    private function findProgramTemplate(string $marketoCampaignType): Program
    {
        $program = $this->programEndpoint->queryByName($this->programTemplateName($marketoCampaignType));
        if ($program) {
            return $program;
        }
        $program = $this->programEndpoint->queryByName(
            $this->programTemplateName($this->fallbackProgramTemplateCampaign)
        );
        if ($program) {
            return $program;
        }
        throw new Exception('Cannot find any appropriate program template!');
    }

    /**
     * Get program template name.
     *
     * @param string $marketoCampaignType
     *
     * @return string
     */
    private function programTemplateName(string $marketoCampaignType): string
    {
        $marketoCampaignType = $this->environment === 'prod' ? $marketoCampaignType : 'Test';

        return implode('-', [
            'NL',
            $this->templatePrefix(),
            $marketoCampaignType,
            'YYYY',
            'MM',
            'DD',
        ]);
    }

    /**
     * Get program name.
     *
     * @param string $marketoCampaignType
     * @param DateTime $date
     *
     * @return string
     */
    private function programName(string $marketoCampaignType, DateTime $date): string
    {
        $prefix = $this->environment === 'prod' ? '' : ' Dev';

        return implode(
            '-',
            array_filter([
                $prefix,
                'NL',
                $this->templatePrefix(),
                $marketoCampaignType,
                $date->format('Y-m-d-A'),
            ])
        );
    }

    /**
     * @param Program $program
     * @param Folder $folder
     * @param $template
     * @return Email
     * @throws Exception
     */
    private function findEmail(Program $program, Folder $folder, $template): Email
    {
        $programAsFolder = $this->folderEndpoint->queryByName(
            $program->name(),
            ['root' => $folder->folderId()]
        );
        $assetsFolder = $this->folderEndpoint->queryByName(
            'Assets',
            ['root' => $programAsFolder->folderId()]
        );

        $emails = $this->emailEndpoint->browse(['folder' => $assetsFolder->folderId()]);
        if (count($emails) !== 1) {
            throw new Exception('There must be only a single email in Assets folder!');
        }

        $email = $emails[0];

        // Load default template v1 and v2.
        $emailTemplate = $this->emailTemplateEndpoint->queryByName($template);

        // Check that the email uses the correct template.
        if (empty($emailTemplate)) {
            throw new Exception('Cannot find email template "SC Media API"');
        }

        // Check that the system uses a supported email template.
        if ($email->template() !== $emailTemplate->id()) {
            throw new Exception('Incorrect email template is used by the email!');
        }

        return $email;
    }
}
