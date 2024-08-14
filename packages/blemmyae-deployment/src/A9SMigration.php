<?php

/**
 * Class which contain all necessary static functions for A9S migration.
 *
 * @author  Anastasia Lukyanova <stacylkv@gmail.com>
 * @license proprietary
 */

declare(strict_types=1);

namespace Cra\BlemmyaeDeployment;

use Cra\BlemmyaeApplications\BlemmyaeApplications;
use Cra\BlemmyaeApplications\CerberusApps;
use Cra\BlemmyaeDeployment\A9SMigration\MigrationHandler;
use Cra\BlemmyaeDeployment\A9SMigration\MigrationMapping;
use Cra\CtEditorial\EditorialCT;
use Cra\CtPeople\PeopleCT;
use Cra\Integrations\WebhookMessenger\ConsumerObjectId;
use Cra\Integrations\WebhookMessenger\Logger\Logger as MigrationLogger;
use Exception;
use Scm\Tools\Logger;
use Scm\Tools\Utils;
use Scm\Tools\WpCore;
use WP_Error;
use WP_Post;
use WP_Term;

class A9SMigration
{
    public const ALERT_DB_NAME = 'wp_msspalert';
    public const CE2E_DB_NAME = 'wp_afternines1';
    public const ALERT_MEDIA_MAPPING_TABLE = 'wp_alert_media_to_media';
    public const CE2E_MEDIA_MAPPING_TABLE = 'wp_ce2e_media_to_media';
    public const ALERT_MEDIA_IN_CONTENT_TABLE = 'wp_alert_media_in_content';
    public const CE2E_MEDIA_IN_CONTENT_TABLE = 'wp_ce2e_media_in_content';
    public const ALERT_MEDIA_TO_MEDIA_IN_CONTENT_TABLE = 'wp_alert_media_to_media_in_content';
    public const CE2E_MEDIA_TO_MEDIA_IN_CONTENT_TABLE = 'wp_ce2e_media_to_media_in_content';
    public const ALERT_AUTHOR_MAPPING_TABLE = 'wp_alert_wp_user_to_people';
    public const CE2E_AUTHOR_MAPPING_TABLE = 'wp_ce2e_wp_user_to_people';
    public const ALERT_CATEGORY_TAX_MAPPING_TABLE = 'wp_alert_category_taxonomy_to_topic_taxonomy';
    public const CE2E_CATEGORY_TAX_MAPPING_TABLE = 'wp_ce2e_category_taxonomy_to_topic_taxonomy';
    public const ALERT_POSTS_MAPPING_TABLE = 'wp_alert_posts_to_editorial';
    public const CE2E_POSTS_MAPPING_TABLE = 'wp_ce2e_posts_to_editorial';
    public const ALERT_ID_COLUMN = 'alert_id';
    public const CE2E_ID_COLUMN = 'ce2e_id';
    public const BLEMMYAE_ID_COLUMN = 'blem_id';

    public const MSSP_CONTENT_TYPE_MAPPING = [
        'Cybersecurity Alert' => 'News',
        'Cybersecurity Guests' => 'Native',
        'Cybersecurity News' => 'News',
        'Cybersecurity Research' => 'Research Article',
        'Podcast' => 'Podcast',
        'top 10' => 'Perspective',
        'videos' => 'Video',
    ];

    public const MSSP_CONTENT_TYPE_PRIORITY = ['Native', 'Podcast', 'News', 'Research Article', 'Video', 'Perspective'];

    public const CE2E_CONTENT_TYPE_MAPPING = [
        'Influencers' => 'Native',
        'News' => 'News',
        'Podcasts' => 'Podcast',
        'CompTIA Podcasts' => 'Podcast',
        'Research' => 'Research Article',
        'Predictions' => 'Perspective',
        'Top 10' => 'News',
        'Videos' => 'Video',
    ];

    public const CE2E_CONTENT_TYPE_PRIORITY = ['Native', 'Podcast', 'News', 'Video', 'Perspective', 'Research Article'];

    protected string $vendor;
    protected string $objectType;
    protected array $objects;
    protected bool $force;
    private array $objectIds;
    private int $objectsCount;
    private MigrationHandler $migrationHandler;

    /**
     * @throws Exception
     */
    public function __construct($vendor, $objectType, $objects, $force = false)
    {
        $this->vendor = $vendor;
        $this->objectType = $objectType;
        $this->objects = $objects;
        $this->objectIds = array_keys($this->objects);
        $this->objectsCount = sizeof($this->objects);
        $this->force = $force;
        $this->migrationHandler = new MigrationHandler(new MigrationLogger());

        $this->upsertData();
    }

    /**
     * @throws Exception
     */
    protected function upsertData(): void
    {
        $count = 1;

        $importedIds = [];
        try {
            $importedItems = MigrationMapping::findMultiple(
                $this->objectIds,
                $this->objectType,
                $this->vendor
            );
            $importedIds = array_map(static fn($item) => $item->id, $importedItems);
        } catch (Exception $exception) {
            Logger::log($exception->getMessage(), 'warning');
        }

        foreach ($this->objects as $object) {
            $msgPrefix = '[' . $count . '/' . $this->objectsCount . ']';

            $objectId = $object->asDataObject()->object->post->ID;

            if (!$this->force && in_array($objectId, $importedIds)) {
                $count++;
                Logger::log($msgPrefix . 'Already processed', 'skip');
                continue;
            }

            $this->migrationHandler->processMessage($object);
            $importedObjectId = MigrationMapping::findById($object->getObjectId())->postId;

            if (empty($importedObjectId)) {
                Logger::log("$msgPrefix Failed to create/update $this->objectType: ", 'notice');
            }

            Logger::log(
                "Processed $msgPrefix, current $this->objectType id: $objectId",
                'status'
            );
            $count++;
        }
    }

    /**
     * Create a string key of webhook object
     *
     * @param ConsumerObjectId $messageObjectId
     *
     * @return string
     */
    public static function migrationKey(ConsumerObjectId $messageObjectId): string
    {
        return implode('-', [
            $messageObjectId->getVendor(),
            $messageObjectId->getType(),
            $messageObjectId->getId(),
        ]);
    }

    public static function saveIdMapping(
        string $tableName,
        string $oldIdColumn,
        $oldId,
        $newId
    ) {
        global $wpdb;

        $newIdColumn = self::BLEMMYAE_ID_COLUMN;

        $checkIfExists =
            $wpdb->get_var("SELECT $oldIdColumn FROM $tableName WHERE $oldIdColumn=$oldId");

        $checkIfSame = null;

        if (!empty($newId)) {
            $checkIfSame =
                $wpdb->get_var(
                    "SELECT $oldIdColumn FROM $tableName WHERE $oldIdColumn=$oldId AND $newIdColumn=$newId"
                );
        }

        if ($checkIfExists !== null && $checkIfSame === null) {
            return $wpdb->update($tableName, [$newIdColumn => $newId], [$oldIdColumn => $oldId]);
        }

        if ($checkIfSame !== null) {
            // Return int if same row already exist.
            return 1;
        }

        return $wpdb->insert($tableName, [$oldIdColumn => $oldId, $newIdColumn => $newId]);
    }

    # @fixme: Doesn't support both ce2e & alert, value may get overriden.
    public static function getAlreadyProcessedIds(
        string $tableName,
        string $oldIdColumn
    ): array {
        global $wpdb;

        $newIdColumn = self::BLEMMYAE_ID_COLUMN;

        return array_column(
            $wpdb->get_results(
                "SELECT $oldIdColumn FROM $tableName WHERE $newIdColumn !=0 ORDER BY $oldIdColumn"
            ),
            'alert_id'
        );
    }

    public static function getAllUsersData(array $ids): array
    {
        $usersData = [];
        foreach ($ids as $id) {
            $userData = get_userdata($id);
            $userDataArray = [
                'id' => $id,
                'display_name' => $userData->display_name,
                'user_email' => $userData->user_email,
            ];
            $userMetadata = get_user_meta($id);
            $userMetadataValues = array_column($userMetadata, 0);
            $userMetadataArray = array_combine(array_keys($userMetadata), $userMetadataValues);
            $usersData[$id] = array_merge($userDataArray, $userMetadataArray);
        }

        return $usersData;
    }

    public static function updateAcfField($selector, $value, $post_id = false): void
    {
        if (!empty($value) && !get_field($selector, $post_id)) {
            update_field($selector, $value, $post_id);
        }
    }

    // Update person ACF fields.
    public static function updatePerson(array $user, int $pageId): void
    {
        $fields = [
            PeopleCT::GROUP_PEOPLE_ADVANCED__FIELD_FIRST_NAME => $user['first_name'] ?? '',
            PeopleCT::GROUP_PEOPLE_ADVANCED__FIELD_LAST_NAME => $user['last_name'] ?? '',
            PeopleCT::GROUP_PEOPLE_ADVANCED__FIELD_EMAIL => $user['user_email'] ?? '',
            PeopleCT::GROUP_PEOPLE_ADVANCED__FIELD_FACEBOOK => $user['facebook'] ?? '',
            PeopleCT::GROUP_PEOPLE_ADVANCED__FIELD_LINKEDIN => $user['linkedin'] ?? '',
            PeopleCT::GROUP_PEOPLE_ADVANCED__FIELD_TWITTER => $user['twitter'] ?? '',
            PeopleCT::GROUP_PEOPLE_ADVANCED__FIELD_BIO => $user['description'] ?? '',
        ];

        foreach ($fields as $field => $value) {
            if (isset($value) && $value !== '') {
                self::updateAcfField($field, $value, $pageId);
            }
        }

        do_action('acf/save_post', $pageId);
    }

    public static function getPostsData(array $ids): array
    {
        $postsData = [];

        foreach ($ids as $id) {
            $postData = get_post($id);

            $postsData[$id] = [
                'post_title' => $postData->post_title,
                'post_date' => $postData->post_date,
                'post_content' => $postData->post_content,
                'post_excerpt' => $postData->post_excerpt,
                'post_status' => $postData->post_status ? 'publish' : 'draft',
                'post_name' => $postData->post_name ?: sanitize_title($postData->post_title),
                'post_author' => $postData->post_author
            ];
        }

        return $postsData;
    }

    public static function getPostsMetaData(array $ids): array
    {
        $postsMeta = [];

        foreach ($ids as $id) {
            $postMeta = get_post_meta($id);
            $postMetaValues = array_column($postMeta, 0);
            $postsMeta[$id] = array_combine(array_keys($postMeta), $postMetaValues);
        }

        return $postsMeta;
    }

    public static function getPostsCategories(array $ids): array
    {
        $postsCategories = [];

        foreach ($ids as $id) {
            $primaryCategory = null;
            $primaryCategoryId = get_post_meta($id, '_yoast_wpseo_primary_category');

            if (!empty($primaryCategoryId) && isset($primaryCategoryId[0]) && $primaryCategoryId[0] !== '') {
                $primaryCategory = get_term_by(
                    'term_id',
                    $primaryCategoryId[0],
                    'category'
                );
            }

            $allCategories = wp_get_post_categories($id, ['fields' => 'all']);

            if (!empty($primaryCategory) && count($allCategories) > 1) {
                $key = array_search($primaryCategory, $allCategories);
                unset($allCategories[$key]);
                array_unshift($allCategories, $primaryCategory);
                $postsCategories[$id] = $allCategories;
            } else {
                $postsCategories[$id] = $allCategories;
            }
        }

        return $postsCategories;
    }

    public static function getMapping(string $entity, string $app, string|int $oldId): array
    {
        global $wpdb;

        // TODO: Remove prefix Alert and Ce2e from constant names. Add prefix separately according to app name.
        $tableName = '';
        $newIdColumn = self::BLEMMYAE_ID_COLUMN;
        $oldIdColumn = '';

        switch ($entity) {
            case 'author':
                switch ($app) {
                    case BlemmyaeApplications::MSSP:
                        $tableName = self::ALERT_AUTHOR_MAPPING_TABLE;
                        break;
                    case BlemmyaeApplications::CE2E:
                        $tableName = self::CE2E_AUTHOR_MAPPING_TABLE;
                        break;
                }
                break;
            case 'category':
                switch ($app) {
                    case BlemmyaeApplications::MSSP:
                        $tableName = self::ALERT_CATEGORY_TAX_MAPPING_TABLE;
                        break;
                    case BlemmyaeApplications::CE2E:
                        $tableName = self::CE2E_CATEGORY_TAX_MAPPING_TABLE;
                        break;
                }
                break;
            case 'media':
                switch ($app) {
                    case BlemmyaeApplications::MSSP:
                        $tableName = self::ALERT_MEDIA_MAPPING_TABLE;
                        break;
                    case BlemmyaeApplications::CE2E:
                        $tableName = self::CE2E_MEDIA_MAPPING_TABLE;
                        break;
                }
                break;
            case 'media_in_content':
                switch ($app) {
                    case BlemmyaeApplications::MSSP:
                        $tableName = self::ALERT_MEDIA_IN_CONTENT_TABLE;
                        break;
                    case BlemmyaeApplications::CE2E:
                        $tableName = self::CE2E_MEDIA_IN_CONTENT_TABLE;
                        break;
                }
                break;
            case 'media_to_media_in_content':
                switch ($app) {
                    case BlemmyaeApplications::MSSP:
                        $tableName = self::ALERT_MEDIA_TO_MEDIA_IN_CONTENT_TABLE;
                        break;
                    case BlemmyaeApplications::CE2E:
                        $tableName = self::CE2E_MEDIA_TO_MEDIA_IN_CONTENT_TABLE;
                        break;
                }
                break;
            case 'post':
                switch ($app) {
                    case BlemmyaeApplications::MSSP:
                        $tableName = self::ALERT_POSTS_MAPPING_TABLE;
                        break;
                    case BlemmyaeApplications::CE2E:
                        $tableName = self::CE2E_POSTS_MAPPING_TABLE;
                        break;
                }
                break;
            default:
                Logger::log("There are no suitable cases for the $entity entity", 'error');
        }

        switch ($app) {
            case BlemmyaeApplications::MSSP:
                $oldIdColumn = self::ALERT_ID_COLUMN;
                break;
            case BlemmyaeApplications::CE2E:
                $oldIdColumn = self::CE2E_ID_COLUMN;
                break;
            default:
                Logger::log("There are no suitable cases for the $entity entity", 'error');
        }


        $newId = array_column(
            $wpdb->get_results(
                "SELECT $newIdColumn FROM $tableName WHERE $oldIdColumn=$oldId"
            ),
            $newIdColumn
        );

        return $newId;
    }

    public static function setApp(\WP_Term $app, \WP_Post $newPost): void
    {
        update_field(
            CerberusApps::APPLICATION_FIELD,
            $app->term_id,
            $newPost->ID
        );
        update_post_meta(
            $newPost->ID,
            CerberusApps::APPLICATION_FIELD_META_KEY,
            $app->term_id
        );
        // Update application slug.
        $applicationSlug = get_field(CerberusApps::APPLICATION_SLUG_FIELD, $newPost->ID);
        if (empty($applicationSlug)) {
            // Update field will trigger update value action => app slug will be generated automatically.
            update_field(CerberusApps::APPLICATION_SLUG_FIELD, '', $newPost->ID);
        }
    }

    /**
     * Upsert Editorial from A9S Post.
     *
     * @param array $options
     * @return WP_Post
     * @throws Exception
     */
    public static function upsertEditorial(array $options): WP_Post
    {
        /* @var $postData array */
        /* @var $postMeta array */
        /* @var $postCategories array */
        /* @var $app WP_Term */
        /* @var $type WP_Term */
        /* @var $brands WP_Term[] */
        /* @var $newId array */
        $postData = $options['post'] ?? null;
        $postMeta = $options['meta'] ?? null;
        $postCategories = $options['categories'] ?? null;
        $app = $options['app'] ?? null;
        $type = $options['type'] ?? null;
        $brands = $options['brand'] ?? null;
        $newId = $options['newId'] ?? null;

        if (!empty($postData)) {
            $data = $postData;
            unset($data['post_excerpt'], $data['post_author']);
            $data['post_type'] = 'editorial';
            $data['post_content'] = self::removeShortcodes($postData['post_content']);
            $newPost = empty($newId) ? WpCore::insertPost($data) : get_post($newId[0]);

            update_field(EditorialCT::GROUP_EDITORIAL_ADVANCED__FIELD_DECK, $postData['post_excerpt'], $newPost->ID);

            // Get new author ID from People mapping table.
            $newAuthor = self::getMapping('author', $app->slug, $postData['post_author']);
            update_field(
                EditorialCT::GROUP_AUTHOR_COLLECTION__FIELD_AUTHOR,
                $newAuthor,
                $newPost->ID
            );
        } else {
            $newPost = get_post($newId[0]);
        }

        if (!empty($postCategories)) {
            // Get all terms mappings.
            $topics = [];
            $a9sCategories = [];
            foreach ($postCategories as $category) {
                $topic = self::getMapping('category', $app->slug, $category->term_id);
                // Save Categories' names to get right Editorial Type.
                $a9sCategories[] = $category->name;
                // If there is no $topic mapping don't try to find parent topic.
                if (!empty($topic) && isset($topic[0])) {
                    $topics[] = (int)$topic[0];
                }
            }
            // Set 'Uncategorized' Parent Topic and 'Unsubcategorized' Topic if, topics array is empty.
            if (empty($topics)) {
                $topics[] = get_term_by('slug', 'unsubcategorized', 'topic')->term_id;
            }

            # @todo: Confirm that values are valid.
            update_field(
                EditorialCT::GROUP_EDITORIAL_TAXONOMY__FIELD_TOPIC,
                $topics,
                $newPost->ID
            );

            // Set Editorial Type field according to category and priority.
            switch ($app->slug) {
                case BlemmyaeApplications::MSSP:
                    $contentTypeMapping = self::MSSP_CONTENT_TYPE_MAPPING;
                    $contentTypePriority = self::MSSP_CONTENT_TYPE_PRIORITY;
                    break;
                case BlemmyaeApplications::CE2E:
                    $contentTypeMapping = self::CE2E_CONTENT_TYPE_MAPPING;
                    $contentTypePriority = self::CE2E_CONTENT_TYPE_PRIORITY;
                    break;
            }

            $matches = array_intersect(array_keys($contentTypeMapping), $a9sCategories);
            if (!empty($matches)) {
                $highestPriorityType = null;

                foreach ($matches as $match) {
                    $mappedType = $contentTypeMapping[$match];
                    if (
                        isset($mappedType) &&
                        (is_null($highestPriorityType) ||
                            array_search($mappedType, $contentTypePriority) <
                            array_search($highestPriorityType, $contentTypePriority)
                        )
                    ) {
                        $highestPriorityType = $mappedType;
                    }
                }
                $typeId = get_term_by('name', $highestPriorityType, EditorialCT::EDITORIAL_TYPE_TAXONOMY)->term_id;
                update_field(
                    EditorialCT::GROUP_EDITORIAL_TAXONOMY__FIELD_TYPE,
                    $typeId,
                    $newPost->ID
                );
            } else {
                update_field(
                    EditorialCT::GROUP_EDITORIAL_TAXONOMY__FIELD_TYPE,
                    $type->term_id,
                    $newPost->ID
                );
            }
        }

        if (!empty($brands) && count($brands) === 2) {
            update_field(
                EditorialCT::GROUP_EDITORIAL_TAXONOMY__FIELD_BRAND,
                [$brands[0]->term_id, $brands[1]->term_id],
                $newPost->ID
            );
        }

        //Get media mappings.
        if (!empty($postMeta) && isset($postMeta['_thumbnail_id'])) {
            $newMedia = self::getMapping('media', $app->slug, $postMeta['_thumbnail_id']);

            if (!empty($newMedia)) {
                update_field(
                    EditorialCT::GROUP_EDITORIAL_ADVANCED__FIELD_FEATURED_IMAGE,
                    array_shift($newMedia),
                    $newPost->ID
                );
                update_field(EditorialCT::GROUP_EDITORIAL_ADVANCED__FIELD_SHOW_FEATURED_IMAGE, 1, $newPost->ID);
            }
        }

        // Save values to Meta ACF.
        // Generate Meta Title like "Title - Site title".
        self::setApp($app, $newPost);

        if (!empty($postData)) {
            $siteTitle = '';
            switch ($app) {
                case BlemmyaeApplications::MSSP:
                    $siteTitle = 'MSSP Alert';
                    break;
                case BlemmyaeApplications::CE2E:
                    $siteTitle = 'Channel E2E';
                    break;
            }
            update_field(
                EditorialCT::GROUP_META__FIELD_TITLE,
                $postData['post_title'] . ' - ' . $siteTitle,
                $newPost->ID
            );
        }

        if (!empty($postMeta) && isset($postMeta['_yoast_wpseo_metadesc'])) {
            update_field(
                EditorialCT::GROUP_META__FIELD_DESCRIPTION,
                $postMeta['_yoast_wpseo_metadesc'],
                $newPost->ID
            );
        }

        do_action('acf/save_post', $newPost->ID);

        return $newPost;
    }

    /**
     * @throws Exception
     */
    public static function upsertPerson(array $user, array $newId): WP_Error|int|null
    {
        if (!empty($newId)) {
            $pageId = (int)array_shift($newId);
        } else {
            /** @phpstan-ignore-next-line */
            $page = get_page_by_title($user['display_name'], OBJECT, 'people');
            $pageId = null;

            if ($page instanceof \WP_POST) {
                $pageId = $page->ID;
                Logger::log('Person already exist, updating person info.', 'notice');
            }

            if (is_array($page)) {
                throw new Exception("There are multiply pages with the same title.");
            }

            if (is_null($page)) {
                $postData = [
                    'post_title' => $user['display_name'],
                    'post_content' => '',
                    'post_status' => 'publish',
                    'post_author' => 0,
                    'post_type' => 'people',
                ];
                $pageId = wp_insert_post($postData);

                if ($pageId instanceof \WP_Error) {
                    throw new Exception("Failed to insert new post: " . $pageId->get_error_message());
                }
            }
        }

        $termType = get_term_by('name', 'Author', 'people_type');
        if ($termType) {
            update_field(PeopleCT::GROUP_PEOPLE_TAXONOMY__FIELD_TYPE, $termType, $pageId);
        }

        // Update fields in case they are empty.
        self::updatePerson($user, $pageId);

        return $pageId;
    }

    /**
     * Generate new attachment and save id mapping.
     *
     * @throws Exception
     */
    public static function createAttachment(
        WP_Post $media,
        string $app,
        string $tableName,
        string $a9sIdColumn
    ): bool|array {
        $relative_path = str_replace(
            'wp-content',
            $app,
            substr(parse_url($media->guid, PHP_URL_PATH), 1)
        );
        $guid = home_url() . '/' . $relative_path;

        $attachment = [
            'post_mime_type' => $media->post_mime_type,
            'post_title' => $media->post_title,
            'post_name' => $media->post_name,
            'post_content' => $media->post_content,
            'post_excerpt' => $media->post_excerpt,
            'post_status' => $media->post_status,
            'guid' => $guid
        ];

        $s3Url = 'https://files.scmagazine.com/' . $relative_path;

        $attach_id = 0;
        try {
            $attach_id = WpCore::mediaHandleSideload($s3Url, '', 0, $attachment);
        } catch (Exception $exception) {
            Logger::log($exception->getMessage(), 'warning');
        }

        // Save old media path to group Media Advanced field Original Source.
        update_field('field_61e529e43808a', $media->guid, $attach_id);
        do_action('acf/save_post', $media->ID);

        // Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
        require_once(ABSPATH . 'wp-admin' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'image.php');

        // Save a9s_id -> blem_id media mapping.
        $result = A9SMigration::saveIdMapping(
            $tableName,
            $a9sIdColumn,
            $media->ID,
            $attach_id
        );

        if (!$result) {
            Logger::log("Could not insert mapping pair for media with ID: $media->ID", 'notice');
            return false;
        }

        return true;
    }

    // @fixme: Remove all usages on hard-release.
    public static function isProd(): bool
    {
        return Utils::isProd();
    }

    public static function removeShortcodes(string $content): string
    {
        // Remove related blocks from post_content.
        $pos = strpos($content, '[sc name="related-start"');
        if ($pos !== false) {
            $content = substr($content, 0, $pos);
        }

        // Remove caption shortcode.
        $regexCaptionStart = '#\[caption[^\]]*\]|\[caption\]/#';
        $regexCaptionEnd = '#\[\/caption\]#';

        preg_match_all($regexCaptionStart, $content, $capMatches);

        foreach ($capMatches[0] as $capMatch) {
            // Save old align attribute.
            preg_match('#align="([^"]+)"#', $capMatch, $align);

            if (isset($align[1])) {
                $newString = '<figure class="' . $align[1] . '">';
                $content = str_replace($capMatch, $newString, $content);
            } else {
                $content = str_replace($capMatch, '<figure>', $content);
            }
        }

        $content = preg_replace($regexCaptionEnd, '</figcaption></figure>', $content);

        $regexImage = '#<img[^>]+>#';
        preg_match($regexImage, $content, $images);
        $tag = '<figcaption>';
        foreach ($images as $image) {
            $content = str_replace($image, $image . $tag, $content);
        }

        // Trim all shortcodes.
        return preg_replace('#\[[^\]]+\]#', '', $content);
    }

    public static function replaceUrl(string $content, string $app): string
    {
        $pattern = match ($app) {
            BlemmyaeApplications::MSSP => '/(href|src)="((https:|http:)\/\/www\.msspalert\.com\/[^"]+)"/',
            BlemmyaeApplications::CE2E => '/(href|src)="((https:|http:)\/\/www\.channele2e\.com\/[^"]+)"/',
        };

        preg_match_all($pattern, $content, $matches);

        if (isset($matches[2])) {
            foreach ($matches[2] as $url) {
                $relativeUrl = parse_url($url, PHP_URL_PATH);
                $content = str_replace($url, $relativeUrl, $content);
            }
        }

        return $content;
    }

    public static function addAttrRelToHyperlink(string $content): string
    {
        // Matches strings like "<a href="https://example.com/example" [attributes]>".
        $pattern = '/<a.*?href="(?!https:\/\/www\.scmagazine\.com)[^"]+".*?>/';

        preg_match_all($pattern, $content, $matches);

        if (isset($matches[0])) {
            foreach ($matches[0] as $tag) {
                preg_match_all('/rel=".*?"/', $tag, $rel);

                if (isset($rel[0]) && !empty($rel[0])) {
                    preg_match_all('/rel=".*?nofollow.*?"/', $tag, $nofollow);
                    if (isset($nofollow[0]) && !empty($nofollow[0])) {
                        continue;
                    } else {
                        $attr = '$1 rel="nofollow ';
                        $result = preg_replace('/rel=".*?/', $attr, $tag);
                    }
                } else {
                    $attr = '$1 rel="nofollow">';
                    $result = preg_replace('/(<a\b[^><]*)>/i', $attr, $tag);
                }

                $content = str_replace($tag, $result, $content);
            }
        }

        return $content;
    }
}
