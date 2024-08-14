<?php

/**
 * Main plugin file
 *
 * @package   Cra\CtLearning
 * @author    Squiz Pty Ltd <products@squiz.net>
 * @copyright 2021 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   proprietary
 */

declare(strict_types=1);

namespace Cra\CtLearning;

use BrightNucleus\Config\ConfigInterface;
use BrightNucleus\Config\ConfigTrait;
use BrightNucleus\Config\Exception\FailedToProcessConfigException;
use BrightNucleus\Settings\Settings;
use Cra\CtPeople\People;
use Cra\CtPeople\PeopleCT;
use DateTime;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Exception;
use Scm\Tools\Logger;
use Scm\Tools\PostByQueryResolver;
use WP_Post;
use WP_Query;

use function add_action;
use function load_plugin_textdomain;

/**
 * Main plugin class.
 *
 * @since 0.1.0
 *
 * @package Cra\CtLearning
 * @author  Gary Jones
 */
class Plugin
{
    use ConfigTrait;

    private const ARCHIVE_OLD_LEARNINGS_CRON = 'ctl_archive_old_learnings';

    private const INTRADO_HOST = 'onlinexperiences.com';

    public const TAXONOMY__VENDOR_TYPE = 'learning_vendor_type';

    public const VENDOR_TYPE__SWOOGO = 'Swoogo';

    public const VENDOR_TYPE__INTRADO = 'Intrado';

    public const VENDOR_TYPE__GO_TO_WEBINAR = 'GoToWebinar';

    public const ACF__LEARNING_VENDOR_TYPE__NAME = 'Learning Vendor Type';

    public const ACF_FC_LAYOUT = 'acf_fc_layout';

    /**
     * Static instance of the plugin.
     *
     * @since 0.1.0
     *
     * @var self
     */
    protected static Plugin $instance;

    /**
     * Associative array with event URL as key and array of event hidden values as value.
     *
     * @var string[][]
     */
    private array $intradoEventHiddenValues = [];

    /**
     * Instantiate a Plugin object.
     *
     * Don't call the constructor directly, use the `Plugin::get_instance()`
     * static method instead.
     *
     * @param ConfigInterface $config Config to parametrize the object.
     *
     * @throws FailedToProcessConfigException If the Config could not be parsed correctly.
     *
     * @since 0.1.0
     *
     */
    public function __construct(ConfigInterface $config)
    {
        $this->processConfig($config);
    }

    /**
     * Launch the initialization process.
     *
     * @since 0.1.0
     */
    public function run(): void
    {
        add_action('plugins_loaded', [$this, 'loadTextDomain']);
        add_filter(
            'acf/update_value/name=show_key',
            [$this, 'updateShowKey'],
            10,
            2
        );
        add_filter(
            'acf/update_value/name=show_package_key',
            [$this, 'updateShowPackageKey'],
            10,
            2
        );
        add_filter(
            'acf/update_value/name=attendee_type_key',
            [$this, 'updateAttendeeTypeKey'],
            10,
            2
        );
        add_filter(
            'acf/update_value/name=affiliate_data',
            [$this, 'updateAffiliateData'],
            10,
            2
        );

        add_action(self::ARCHIVE_OLD_LEARNINGS_CRON, [$this, 'archiveOldLearnings']);
        if (!wp_next_scheduled(self::ARCHIVE_OLD_LEARNINGS_CRON)) {
            wp_schedule_event(time(), 'weekly', self::ARCHIVE_OLD_LEARNINGS_CRON);
        }

        add_action('acf/save_post', [$this, 'setLearningVendorType'], 10, 1);

        new PostByQueryResolver(LearningCT::POST_TYPE);
        new PostByQueryResolver(SessionCT::POST_TYPE);

        new LearningCT();
        new SessionCT();
    }

    /**
     * Load the plugin text domain.
     *
     * @since 0.1.0
     */
    public function loadTextDomain(): void
    {
        /**
         * Plugin text domain.
         *
         * @var string $textDomain
         */
        $textDomain = $this->config->getKey('Plugin.textdomain');
        $languagesDir = 'languages';
        if ($this->config->hasKey('Plugin/languages_dir')) {
            /**
             * Directory path.
             *
             * @var string $languagesDir
             */
            $languagesDir = $this->config->getKey('Plugin.languages_dir');
        }

        load_plugin_textdomain($textDomain, false, $textDomain . '/' . $languagesDir);
    }

    /**
     * Callback for 'acf/update_value/name=show_key' filter.
     *
     * @param string|null $value
     * @param int $postId
     *
     * @return string
     * @throws Exception
     */
    public function updateShowKey(?string $value, int $postId): string
    {
        return $this->getIntradoFieldValue($postId, 'ShowKey', $value ?? '');
    }

    /**
     * Callback for 'acf/update_value/name=show_package_key' filter.
     *
     * @param string|null $value
     * @param int $postId
     *
     * @return string
     * @throws Exception
     */
    public function updateShowPackageKey(?string $value, int $postId): string
    {
        return $this->getIntradoFieldValue($postId, 'ShowPackageKey', $value ?? '');
    }

    /**
     * Callback for 'acf/update_value/name=attendee_type_key' filter.
     *
     * @param string|null $value
     * @param int $postId
     *
     * @return string
     * @throws Exception
     */
    public function updateAttendeeTypeKey(?string $value, int $postId): string
    {
        return $this->getIntradoFieldValue($postId, 'AttendeeTypeKey', $value ?? '');
    }

    /**
     * Callback for 'acf/update_value/name=affiliate_data' filter.
     *
     * @param string|null $value
     * @param int $postId
     *
     * @return string
     * @throws Exception
     */
    public function updateAffiliateData(?string $value, int $postId): string
    {
        return $this->getIntradoFieldValue($postId, 'AffiliateData', $value ?? '');
    }

    /**
     * Get Intrado field value from the event page.
     *
     * @param int $postId
     *  Post id.
     * @param string $field
     *  Intrado field name.
     * @param string $defaultValue
     *  Default field value for intrado, which will be return for empty event url.
     *
     * @return string
     * @throws Exception
     */
    private function getIntradoFieldValue(
        int $postId,
        string $field,
        string $defaultValue = ''
    ): string {
        $rawEventUrl = trim((string)get_field('vendor_0_event_url', $postId));

        // For empty event URL -> return default value.
        if (empty($rawEventUrl)) {
            return $defaultValue;
        }

        $eventUrl = $this->prepareIntradoEventUrl($rawEventUrl);
        if (empty($eventUrl) || $eventUrl !== $rawEventUrl) {
            update_field('vendor_0_event_url', $eventUrl, $postId);
        }
        if (empty($eventUrl)) {
            return '';
        }

        $values = $this->fetchIntradoHiddenValues($eventUrl);

        return $values[$field] ?? '';
    }

    /**
     * Prepare proper Intrado event URL from meta field value.
     *
     * @param string $rawUrl
     *
     * @return string
     */
    private function prepareIntradoEventUrl(string $rawUrl): string
    {
        $registerQuery = $this->parseUrlQuery($rawUrl);
        if (empty($registerQuery['ShowUUID'])) {
            $showUuid = $this->parseShowUuid($rawUrl);
            if (empty($showUuid)) {
                return '';
            }
            $registerQuery['ShowUUID'] = $showUuid;
        }
        $baseUrl = 'https://' . self::INTRADO_HOST . '/scripts/Server.nxp?';
        $query = [
            'LASCmd' => 'AI:4;F:QS!10100',
            'ShowUUID' => $registerQuery['ShowUUID'],
            'AffiliateData' => $registerQuery['AffiliateData'] ?? null,
        ];

        return $baseUrl . http_build_query($query);
    }

    /**
     * Parse URL query and return as associative array.
     *
     * @param string $url
     *
     * @return array
     */
    private function parseUrlQuery(string $url): array
    {
        $urlParts = parse_url($url);
        if ($urlParts['host'] !== self::INTRADO_HOST) {
            return [];
        }
        parse_str($urlParts['query'] ?? '', $query);

        return $query;
    }

    /**
     * Parse ShowUUID from registration URL.
     *
     * @param string $url
     *
     * @return string
     */
    private function parseShowUuid(string $url): string
    {
        $regex = '/ShowUUID=(\w{8}-\w{4}-\w{4}-\w{4}-\w{12})/';
        preg_match_all($regex, $url, $matches);

        return $matches[1][0] ?? '';
    }

    /**
     * Get Intrado event hidden values from the event page.
     *
     * @param string $eventUrl
     *
     * @return array
     * @throws Exception
     */
    private function fetchIntradoHiddenValues(string $eventUrl): array
    {
        if (isset($this->intradoEventHiddenValues[$eventUrl])) {
            return $this->intradoEventHiddenValues[$eventUrl];
        }

        $response = wp_remote_request($eventUrl);
        if (!$response && is_wp_error($response)) {
            throw new Exception($response->get_error_message());
        }
        $document = new DOMDocument('1.0', 'UTF-8');
        // Omitting errors regarding broken Intrado HTML.
        @$document->loadHTML($response['body']);
        $xpath = new DOMXPath($document);

        $values = [];
        foreach ($xpath->query('/html/body/form//input[@type="hidden"]') as $input) {
            /** @var DOMElement $input */
            // @todo review deprecated
            $inputValue = filter_var(trim($input->getAttribute('value')), FILTER_SANITIZE_STRING);
            $values[$input->getAttribute('name')] =
                strtoupper($inputValue) !== 'NULL' ? $inputValue : null;
        }
        $this->intradoEventHiddenValues[$eventUrl] = $values;

        return $values;
    }

    /**
     * Cron job callback which archives old on-demand events.
     */
    public function archiveOldLearnings(): void
    {
        $query = new WP_Query(
            [
                'post_type' => 'learning',
                'nopaging' => true,
                'meta_key' => 'date_0_start_date',
                'meta_value' => (new DateTime('-1 year'))->format('Y-m-d H:i:s'),
                'meta_compare' => '<=',
            ]
        );
        while ($query->have_posts()) {
            $post = $query->post;
            $post->post_status = 'archive';
            wp_update_post($post);
            $query->next_post();
        }
    }

    /**
     * Callback for learning post type save hook.
     * Sets 'learning_vendor_type' according to vendor chosen in a Swoogo Event Mapper.
     * If no vendor provided, sets to null.
     *
     * @param int|string $postId Post ID coming from ACF. Might be a string if saving options.
     */
    public function setLearningVendorType(mixed $postId): void
    {
        switch (get_post_type($postId)) {
            case LearningCT::POST_TYPE:
                $vendor = get_field(LearningCT::GROUP_LEARNING_ADVANCED__FIELD_VENDOR, $postId, false);
                $vendorField = LearningCT::GROUP_LEARNING_ADVANCED__FIELD_VENDOR_TYPE;
                self::convertGutenbergSpeakerBlockToPersonReference($postId);
                break;
            case SessionCT::POST_TYPE:
                $vendor = get_field(SessionCT::GROUP_SESSION_ADVANCED__FIELD_VENDOR, $postId, false);
                $vendorField = SessionCT::GROUP_SESSION_ADVANCED__FIELD_VENDOR_TYPE;
                break;
            default:
                return;
        }

        if (empty($vendor)) {
            update_field($vendorField, null, $postId);
            return;
        }

        $terms = array_flip(
        /** @phpstan-ignore-next-line */
            acfe_get_taxonomy_terms_ids(self::TAXONOMY__VENDOR_TYPE)[self::ACF__LEARNING_VENDOR_TYPE__NAME]
        );

        $type = $vendor[0][self::ACF_FC_LAYOUT];
        switch ($type) {
            case 'swoogo':
                update_field($vendorField, $terms[self::VENDOR_TYPE__SWOOGO], $postId);
                break;
            case 'intrado':
                update_field($vendorField, $terms[self::VENDOR_TYPE__INTRADO], $postId);
                break;
            case 'gotowebinar':
                update_field($vendorField, $terms[self::VENDOR_TYPE__GO_TO_WEBINAR], $postId);
                break;
            default:
                break;
        }
    }

    /**
     * Create persons from speaker card in event body.
     * Delete when move from Intrado and speakers can be pulled to the corresponding field.
     *
     * @param int|string $postId
     *
     * @return void
     */
    public static function convertGutenbergSpeakerBlockToPersonReference(int|string $postId): void
    {
        $post = get_post($postId);
        $postContent = $post->post_content;

        // Get existing event's speakers.
        $eventSpeakers = get_field(LearningCT::GROUP_LEARNING_ADVANCED__FIELD_SPEAKERS, $postId);

        // Get all gutenberg speaker blocks from post's body and image id to each of them.
        $matches = [];
        $pattern = '/<!-- wp:cra\\/webcast-speaker\s*(\{[^}]*})\s*-->(.*?)<!-- \\/wp:cra\\/webcast-speaker -->/s';
        preg_match_all($pattern, $post->post_content, $matches);

        if (empty($matches)) {
            return;
        }

        foreach ($matches[2] as $index => $match) {
            // Gather speaker data into 1 array.
            $speaker = [];

            // Parse html fragment to get sought fields.
            $dom = new DomDocument();
            @$dom->loadHTML($match);
            $finder = new DomXPath($dom);

            foreach (['name', 'job', 'company', 'description'] as $field) {
                $value = $finder->query(self::getQueryClassName("speaker-$field"));
                $speaker[$field] = $value->item(0) ? $value->item(0)->nodeValue : '';
            }

            // Check if there are entities of this person.
            $args = [
                'title' => $speaker['name'],
                'post_type' => 'people',
                'post_status' => 'publish',
                'paged' => 0,
            ];
            $duplicates = new WP_Query($args);

            // Skip if person already exists.
            if ($duplicates->have_posts()) {
                foreach ($duplicates->get_posts() as $person) {
                    $personObject = [
                        'speaker' => [$person],
                        'name' => null,
                        'job_title' => null,
                        'company' => null,
                        'bio' => null,
                        'headshot' => null,
                        'link' => null,
                    ];

                    if ($eventSpeakers && !self::speakerExists($eventSpeakers, $personObject)) {
                        $eventSpeakers[] = $personObject;

                        // Remove speakers cards from post body.
                        $postContent = str_replace($matches[0][$index], '', $postContent);
                    }

                    // Update referenced person's type if he is not a speaker yet.
                    $personTypes = get_field(PeopleCT::GROUP_PEOPLE_TAXONOMY__FIELD_TYPE, $person->ID, false);
                    if (
                        is_array($personTypes) && !in_array(
                            PeopleCT::TERM__SPEAKER__ID,
                            $personTypes
                        )
                    ) {
                        $personTypes[] = PeopleCT::TERM__SPEAKER__ID;
                        update_field(PeopleCT::GROUP_PEOPLE_TAXONOMY__FIELD_TYPE, $personTypes, $person->ID);
                        wp_set_post_terms($person->ID, $personTypes, PeopleCT::TAXONOMY__PEOPLE_TYPE);
                    }
                }

                continue;
            }

            // Get the image id mentioned above.
            $key = array_search($match, $matches[2]);
            $headshot = $matches[1][$key];
            $headshot = explode(':', str_replace('}', '', $headshot));
            $speaker['headshot'] = !empty($headshot) ? get_post($headshot[1]) : [];

            // Create speaker.
            $args = [
                'post_title' => $speaker['name'],
                'post_status' => 'publish',
                'post_type' => 'people',
            ];
            $speakerId = wp_insert_post($args);

            Logger::log("Speaker with ID $speakerId was created", 'status');

            // Parse name so the prefixes like 'Dr.' or similar go to first name field.
            $name = explode(' ', $speaker['name']);
            $speaker['last_name'] = sizeof($name) > 1 ? array_pop($name) : '';
            $speaker['first_name'] = implode(' ', $name);

            // Get company entity by name and get its data if it exists.
            $args = [
                'title' => $speaker['company'],
                'post_type' => 'company_profile',
                'post_status' => 'publish',
                'paged' => 0,
            ];
            $companyPost = new WP_Query($args);

            if ($speaker['company'] || $speaker['job']) {
                $speaker['company'] = [
                    [
                        'company' => $companyPost->post ?? null,
                        'job_title' => $speaker['job'],
                        'job_title_taxonomy' => null
                    ],
                ];
            }

            // Update corresponding speaker's fields.
            update_field(PeopleCT::GROUP_PEOPLE_ADVANCED__FIELD_BIO, $speaker['description'], $speakerId);
            update_field(PeopleCT::GROUP_PEOPLE_ADVANCED__FIELD_HEADSHOT, $speaker['headshot'], $speakerId);
            update_field(PeopleCT::GROUP_PEOPLE_ADVANCED__FIELD_FIRST_NAME, $speaker['first_name'], $speakerId);
            update_field(PeopleCT::GROUP_PEOPLE_ADVANCED__FIELD_LAST_NAME, $speaker['last_name'], $speakerId);
            update_field(
                PeopleCT::GROUP_PEOPLE_TAXONOMY__FIELD_TYPE,
                [PeopleCT::TERM__SPEAKER__ID],
                $speakerId
            );
            wp_set_post_terms(
                $speakerId,
                [PeopleCT::TERM__SPEAKER__ID],
                PeopleCT::TAXONOMY__PEOPLE_TYPE
            );
            /** @phpstan-ignore-next-line */
            update_field(People::FIELD__SPEAKER_COLLECTION, $post, $speakerId);
            update_field(PeopleCT::GROUP_PEOPLE_ADVANCED__FIELD_COMPANIES, $speaker['company'], $speakerId);

            // Gather speakers in proper format to update event's field later.
            $eventSpeakers[] = [
                'speaker' => [get_post($speakerId)],
                'name' => null,
                'job_title' => null,
                'company' => null,
                'bio' => null,
                'headshot' => null,
                'link' => null,
            ];
        }

        // Cleanup speakers block title from post content.
        $matches = [];
        $pattern = '/<!-- wp:paragraph -->\s<p><strong>Speakers(.*?):(.*?)<!-- \/wp:paragraph -->/s';
        preg_match_all($pattern, $post->post_content, $matches);
        foreach ($matches[0] as $match) {
            $postContent = str_replace($match, '', $postContent);
        }

        // Update event.
        wp_update_post(['ID' => $postId, 'post_content' => $postContent]);
        update_field(LearningCT::GROUP_LEARNING_ADVANCED__FIELD_SPEAKERS, $eventSpeakers, $postId);
    }

    /**
     * Generate query for getting element by classname in HTML fragment.
     *
     * @param string $classname
     *
     * @return string
     */
    private static function getQueryClassName(string $classname): string
    {
        return "//*[contains(concat(' ', normalize-space(@class), ' '), '$classname')]";
    }

    /**
     * Checks if speaker already exists in the Speakers(performers) field.
     *
     * @param array $speakersArray
     * @param array $speaker
     *
     * @return bool
     */
    private static function speakerExists(array $speakersArray, array $speaker): bool
    {
        foreach ($speakersArray as $speakerObject) {
            if (
                isset($speakerObject['speaker'])
                && isset($speaker['speaker'])
                && $speakerObject['speaker'][0] instanceof WP_Post
                && $speaker['speaker'][0] instanceof WP_Post
            ) {
                if ($speakerObject['speaker'][0]->ID === $speaker['speaker'][0]->ID) {
                    return true;
                }
            }
        }

        return false;
    }
}
