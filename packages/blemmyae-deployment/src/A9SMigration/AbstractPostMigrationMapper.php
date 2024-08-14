<?php

/**
 * @licence proprietary
 *
 * @author Konstantin Gusev <guvkon.net@icloud.com>
 * @author  Alexander Kucherov <avdkucherov@gmail.com>
 */

/** @noinspection PhpUnused */

declare(strict_types=1);

namespace Cra\BlemmyaeDeployment\A9SMigration;

use Cra\BlemmyaeDeployment\A9SMigration;
use Cra\Integrations\WebhookMessenger\ConsumerMapperInterface;
use Cra\Integrations\WebhookMessenger\ConsumerMessageInterface;
use Cra\Integrations\WebhookMessenger\ConsumerObjectId;
use Cra\Integrations\WebhookMessenger\EntityId;
use Cra\Integrations\WebhookMessenger\ProcessedMessage;
use Cra\Integrations\WebhookMessenger\ProcessedMessageInterface;
use Cra\WebhookConsumer\BlemmyaeWebhookConsumerStreamConnector;
use Cra\WebhookConsumer\Logger;
use DateTimeZone;
use Exception;
use Psr\Log\LoggerInterface;
use Red_Item;
use Redirection_Admin;
use Scm\Entity\Media;
use Scm\Tools\Redirects;
use Scm\Tools\WpCore;
use Scm\WP_GraphQL\Options;
use WP_Post;
use WP_Query;
use WP_Term;

/**
 * Abstract migration mapper class for WordPress posts.
 */
abstract class AbstractPostMigrationMapper implements ConsumerMapperInterface
{
    /**
     * @var MigrationMapping[]
     */
    private array $migrationMappings = [];

    protected LoggerInterface $logger;

    protected ConsumerObjectId $id;

    protected object $object;

    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->logger = new Logger();
        if (defined('REDIRECTION_FILE')) {
            $redirectionPluginPath = plugin_dir_path(REDIRECTION_FILE);
            if ($redirectionPluginPath) {
                require_once $redirectionPluginPath . 'redirection-admin.php';
                /** @phpstan-ignore-next-line */
                Redirection_Admin::init();
            }
        }
    }

    /**
     * @inheritDoc
     */
    final public function isObjectUptoDate(ConsumerObjectId $id, int $timestamp): bool
    {
        $migrationMapping = $this->migrationMapping($id);
        if (!$migrationMapping) {
            return false;
        }

        return $migrationMapping->timestamp >= $timestamp;
    }

    /**
     * @inheritDoc
     */
    final public function create(ConsumerObjectId $id, int $timestamp, object $object): void
    {
        $this->id = $id;
        $this->object = $object;
        $migrationMapping = $this->migrationMapping($id) ?? new MigrationMapping($id);
        $entityId = $this->upsert($id, $timestamp, $object);
        $migrationMapping->postId = $entityId->id;
        $migrationMapping->entityType = $entityId->type;
        $migrationMapping->timestamp = $timestamp;
        $migrationMapping->object = $object;
        $migrationMapping->upsert();
        $this->migrationMappings[(string)$id] = $migrationMapping;
    }

    /**
     * @inheritDoc
     */
    final public function update(ConsumerObjectId $id, int $timestamp, object $object): void
    {
        $this->create($id, $timestamp, $object);
    }

    /**
     * @inheritDoc
     */
    final public function delete(ConsumerObjectId $id, int $timestamp, object $object): void
    {
        $this->id = $id;
        $this->object = $object;
        $migrationMapping = $this->migrationMapping($id) ?? new MigrationMapping($id);
        if ($migrationMapping->postId) {
            if (!wp_delete_post($migrationMapping->postId)) {
                throw new Exception(
                    "Unable to delete post (ID: $migrationMapping->postId) of migration object $id"
                );
            }
        }
        $migrationMapping->postId = 0;
        $migrationMapping->entityType = '';
        $migrationMapping->timestamp = $timestamp;
        $migrationMapping->object = $object;
        $migrationMapping->upsert();
        $this->migrationMappings[(string)$id] = $migrationMapping;
    }

    /**
     * Get WP post ID by migration object ID.
     *
     * @param ConsumerObjectId $id
     *
     * @return int Post ID or 0 if it doesn't exist.
     */
    protected function postId(ConsumerObjectId $id): int
    {
        $migrationMapping = $this->migrationMapping($id);
        return $migrationMapping->postId ?? 0;
    }

    /**
     * Get migration mapping by migration object ID.
     *
     * @param ConsumerObjectId $id Webhook object ID
     *
     * @return MigrationMapping|null
     */
    protected function migrationMapping(ConsumerObjectId $id): ?MigrationMapping
    {
        if (!array_key_exists((string)$id, $this->migrationMappings)) {
            $this->migrationMappings[(string)$id] = MigrationMapping::findById($id);
        }

        return $this->migrationMappings[(string)$id];
    }

    /**
     * @inheritDoc
     */
    abstract public function upsert(ConsumerObjectId $id, int $timestamp, object $object): EntityId;

    /**
     * @inheritDoc
     */
    public function getProcessedMessage(
        ConsumerMessageInterface $message,
        bool $isSkipped
    ): ProcessedMessageInterface { # @todo: fix me.
        $migrationMapping = $this->migrationMapping($message->getObjectId());

        return new ProcessedMessage(
            $message, # @todo: fix me.
            [
                'postId' => $message->getEvent() === 'delete' ?
                    'N/A' : (string)$migrationMapping?->postId, # @todo: fix me.
                'postType' => $this->wpEntityBundle(),
                'status' => $isSkipped ? 'skipped' : 'processed',
            ]
        );
    }

    /**
     * @inheritDoc
     */
    abstract public function wpEntityBundle(): string;

    /**
     * Upsert post.
     *
     * @param ConsumerObjectId $id
     * @param int $timestamp Timestamp in milliseconds.
     * @param array $postarr Post data array as defined by wp_insert_post().
     *
     * @return int Returns post ID.
     * @throws Exception
     * @see \wp_insert_post()
     */
    protected function upsertPost(ConsumerObjectId $id, int $timestamp, array $postarr): int
    {
        $migrationMapping = $this->migrationMapping($id);
        $postarr += [
            'ID' => $migrationMapping ? $migrationMapping->postId : 0,
            'post_status' => 'publish',
            'post_type' => $this->wpEntityBundle(),
        ];
        $postExists = !empty($postarr['ID']);
        if ($postExists) {
            // Do not change status of a post if it's already in DB.
            $existingPost = get_post($postarr['ID']);
            if (!($existingPost instanceof WP_Post)) {
                throw new Exception('Cannot find existing post! ID = ' . $postarr['ID']);
            }
            $postarr['post_status'] = $existingPost->post_status;
        }
        if (empty($postarr['post_date_gmt'])) {
            $postarr['post_date_gmt'] = date_create('now', new DateTimeZone('UTC'))
                ->setTimestamp((int)($timestamp / 1000))
                ->format('c');
        }

        return $this->upsertAnyPost($postarr, $postExists, A9SMigration::migrationKey($id));
    }

    /**
     * Wrapper around \wp_insert_post().
     *
     * @param array $postarr
     * @param bool $postExists
     * @param string $mappingKey
     *
     * @return int
     * @throws Exception
     * @see \wp_insert_post()
     */
    protected function upsertAnyPost(array $postarr, bool $postExists = false, string $mappingKey = ''): int
    {
        $postarr += [
            'post_name' => $this->generateUniquePostSlug(
                $postarr['post_title'] ?? '',
                $postarr['ID'] ?? null
            ),
        ];
        $postId = wp_insert_post($postarr, true);
        if (is_wp_error($postId)) {
            throw new Exception($postId->get_error_message());
        }
        if (!$postId) {
            throw new Exception('Unable to save WP post!');
        }

        if ($postExists) {
            // phpcs:ignore
            #do_action(BlemmyaeWebhookConsumerStreamConnector::ACTION_WEBHOOK_MESSAGE_POST_UPDATED, $mappingKey, $postId);

            return (int)$postId;
        }

        #do_action(BlemmyaeWebhookConsumerStreamConnector::ACTION_WEBHOOK_MESSAGE_POST_CREATED, $mappingKey, $postId);

        return (int)$postId;
    }

    /**
     * Generate unique slug based on title.
     *
     * @param string $title
     * @param int|null $postId
     *
     * @return string
     * @throws Exception
     */
    protected function generateUniquePostSlug(string $title, ?int $postId = null): string
    {
        $slug = sanitize_title($title);
        if ($this->isPostSlugUnique($slug, $postId)) {
            return $slug;
        }
        // This happens for already bad content from a vendor, so it's ok to create a bad slug.
        $slug .= '-' . $this->id->getId();
        if ($this->isPostSlugUnique($slug, $postId)) {
            return $slug;
        }
        throw new Exception("Cannot create a unique slug for $title");
    }

    /**
     * Check if post slug is unique.
     *
     * @param string $slug
     * @param int|null $postId
     *
     * @return bool
     */
    private function isPostSlugUnique(string $slug, ?int $postId = null): bool
    {
        $args = [
            'name' => $slug,
            'post_type' => $this->wpEntityBundle(),
            'post_status' => Options::getPublicPostStatuses(),
        ];
        if ($postId) {
            $args['post__not_in'] = [$postId];
        }
        $query = new WP_Query($args);
        return $query->post_count === 0;
    }

    /**
     * Upsert taxonomy term.
     *
     * @param ConsumerObjectId $id
     * @param string $name
     *
     * @return int Returns taxonomy term ID.
     * @throws Exception
     */
    protected function upsertTaxonomyTerm(ConsumerObjectId $id, string $name): int
    {
        $migrationMapping = $this->migrationMapping($id);

        $termId = $migrationMapping?->postId;

        return $termId ?: $this->insertAnyTaxonomyTerm($this->wpEntityBundle(), $name, A9SMigration::migrationKey($id));
    }

    /**
     * Find taxonomy term or insert it if missing.
     *
     * @param string $taxonomy
     * @param string $name
     *
     * @return int
     * @throws Exception
     */
    protected function ensureTaxonomyTerm(string $taxonomy, string $name): int
    {
        $term = get_term_by('name', $name, $taxonomy, OBJECT, 'db');
        return $term instanceof WP_Term ?
            (int)$term->term_id :
            $this->insertAnyTaxonomyTerm($taxonomy, $name);
    }

    /**
     * Insert taxonomy term into any taxonomy.
     *
     * @param string $taxonomy
     * @param string $name
     * @param string $messageKey
     * @param bool $triggerAction
     * @return int
     * @throws Exception
     */
    protected function insertAnyTaxonomyTerm(
        string $taxonomy,
        string $name,
        string $messageKey = '',
        bool $triggerAction = false
    ): int {
        $result = wp_insert_term($name, $taxonomy);
        if (is_wp_error($result)) {
            $termId = $result->get_error_data('term_exists');
            if ($termId) {
                return (int)$termId;
            }
            throw new Exception($result->get_error_message());
        }
        $termId = $result['term_id'] ?? null;
        if (!$termId) {
            throw new Exception('Unable to save taxonomy term!');
        }

        if ($messageKey && $triggerAction) {
            do_action(
                BlemmyaeWebhookConsumerStreamConnector::ACTION_WEBHOOK_MESSAGE_TERM_CREATED,
                $messageKey,
                $termId
            );
        }

        return (int)$termId;
    }

    /**
     * Update terms on field and attach taxonomy terms to post.
     *
     * @param string $field ACF field machine name.
     * @param string $taxonomy Taxonomy name.
     * @param int|string|string[]|int[]|null $terms Term ID or IDs.
     *                  Pass an empty value to remove $taxonomy terms from the post.
     * @param int $postId
     *
     * @return void
     * @throws Exception
     */
    protected function updateTermsOnPost(
        string $field,
        string $taxonomy,
        int|string|array|null $terms,
        int $postId
    ): void { // phpcs:ignore
        WpCore::setPostTerms($taxonomy, $terms, $postId, $field);
    }

    /**
     * Updates taxonomy field with a given values (terms).
     *
     * If no terms found and no default term provided, field will not be updated.
     * Sets the post terms to a given|default terms.
     *
     * @param int $postId Post ID.
     * @param string $field ACF Field key.
     * @param string $taxonomy Taxonomy name.
     * @param array $terms Array of terms to set to post.
     * @param string $defaultTerm If no term found, this value will be used.
     *
     * @return $this
     * @throws Exception
     */
    protected function updateTermsOnTaxonomyField(
        int $postId,
        string $field,
        string $taxonomy,
        array $terms,
        string $defaultTerm = ''
    ): self {
        $outputTerms = [];
        foreach ($terms as $termName) {
            $term = get_term_by('name', $termName, $taxonomy);
            if (!is_wp_error($term) && !empty($term)) {
                $outputTerms[] = $term->term_id;
            }
        }
        if (empty($outputTerms)) {
            $default = get_term_by('name', $defaultTerm, $taxonomy);
            if (!is_wp_error($default) && !empty($default)) {
                $outputTerms[] = $default->term_id;
            }
        }
        if (!empty($outputTerms)) {
            $this->updateTermsOnPost(
                $field,
                $taxonomy,
                $outputTerms,
                $postId
            );
        }

        return $this;
    }

    /**
     * Create image attachment from a URL.
     *
     * @param string $url
     * @param string $description
     *
     * @return int
     * @throws Exception
     */
    protected function createImageAttachmentFromUrl(string $url, string $description = ''): int
    {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        if (empty($url)) {
            throw new Exception('Missing URL for the image.');
        }

        $file = [
            'name' => wp_basename($url),
            'tmp_name' => download_url($url),
        ];

        if (is_wp_error($file['tmp_name'])) {
            throw new Exception($file['tmp_name']->get_error_message());
        }

        $id = media_handle_sideload($file, 0, $description);

        if (is_wp_error($id)) {
            unlink($file['tmp_name']);

            throw new Exception($id->get_error_message());
        }

        return (int)$id;
    }

    /**
     * Updates|Inserts 'Learning' or 'Session' post type post.
     *
     * @param ConsumerObjectId $id
     * @param int $timestamp
     * @param string $title
     * @param string $content
     *
     * @return int
     * @throws Exception
     */
    protected function upsertLearningPost(ConsumerObjectId $id, int $timestamp, string $title, string $content): int
    {
        $migrationMapping = $this->migrationMapping($id);
        $postId = $migrationMapping ? $migrationMapping->postId : 0;

        $postId = $postId ?
            wp_update_post([
                'ID' => $postId,
                'post_title' => $title,
                'post_content' => $content,
            ]) :
            $this->upsertPost($id, $timestamp, [
                'post_title' => $title,
                'post_content' => $content,
                'post_status' => 'draft',
            ]);

        return !(is_wp_error($postId)) ? $postId : 0;
    }

    /**
     * Updates Location field for 'Learning' or 'Session' post type post.
     *
     * @param string $fieldKey ACF 'Location' field key. Same for sessions and learnings.
     * @param int $postId
     * @param object|null $location
     * @param string|null $virtualLocation
     *
     * @return $this
     */
    protected function updateLearningPostLocationField(
        string $fieldKey,
        int $postId,
        ?object $location,
        ?string $virtualLocation = null
    ): self {
        update_field(
            $fieldKey,
            [
                'url' => $virtualLocation ?
                    [
                        'title' => 'Virtual location',
                        'url' => $virtualLocation,
                        'target' => '_blank',
                    ] : null,
                'address' => !empty($location) ?
                    [
                        'name' => $location->name,
                        'street' => $location->street,
                        'locality' => $location->locality,
                        'postal' => $location->postal,
                        'region' => $location->region,
                        'country' => $location->country,
                    ] : null,
                'map' => null,
                // @todo add google map when its support will be added.
            ],
            $postId
        );

        return $this;
    }

    /**
     * Updates 'Speakers' field for 'Learning' or 'Session' post type post.
     *
     * @param string $fieldKey ACF 'Speakers' field key.
     *                    Depends on post type, different for sessions and learnings.
     *                    For 'Learning' this field is a container which includes overrides.
     * @param int $postId
     * @param array $speakerMappings
     * @param array $currentSpeakers
     * @param string|null $innerSpeakerField 'Speakers' -> 'Speaker'. Defined for 'Learnings' only.
     *
     * @return $this
     */
    protected function updateLearningPostSpeakers(
        string $fieldKey,
        int $postId,
        array $speakerMappings,
        array $currentSpeakers,
        string $innerSpeakerField = null
    ): self {
        $innerSpeakerField = $innerSpeakerField ?: $fieldKey;
        $speakers = [];
        foreach ($speakerMappings as $speaker) {
            $id = $speaker->postId;
            // If inner speaker field exists, we have to add one more nesting level for correct ACF update.
            $speakers[$id] = ($innerSpeakerField === $fieldKey) ? $id : ['speaker' => [$id]];
        }
        foreach ($currentSpeakers as $speakerField) {
            $id = $speakerField[$innerSpeakerField][0];
            $speakers[$id] = ($innerSpeakerField === $fieldKey) ? $id : ['speaker' => [$id]];
        }
        update_field(
            $fieldKey,
            array_values($speakers),
            $postId
        );

        return $this;
    }

    /**
     * Update image field.
     *
     * @param int|string $acfEntityId e.g. post ID, "{TAXONOMY}_{TERM_ID}"
     * @param string $imageUrl
     * @param string $field
     * @param string $description
     *
     * @return int|null
     */
    protected function updateImageField(
        $acfEntityId,
        string $imageUrl,
        string $field,
        string $description = ''
    ): ?int { // phpcs:ignore
        if (empty($imageUrl)) {
            update_field($field, null, $acfEntityId);

            return null;
        }

        try {
            $image = get_field($field, $acfEntityId);
            $imageId = $image ? (int)$image['ID'] : null;
            $newImageIds = $this->upsertImages(
                $imageId ? [$imageId] : [],
                [$imageUrl],
                $description
            );
            $newImageId = $newImageIds[0] ?? null;
            if ($newImageId !== $imageId) {
                update_field($field, $newImageId, $acfEntityId);
            }

            return $newImageId;
        } catch (Exception $exception) {
            $this->logger->warning(
                'Error uploading image.',
                ['exception' => $exception->getMessage()]
            );

            return null;
        }
    }

    /**
     * Upsert images into WordPress.
     *
     * @param array|int[] $imageIds IDs of the existing images in WordPress.
     * @param array|string[] $imageUrls Image URLs to upsert into WordPress.
     * @param string $description (optional) Description for inserted images.
     *
     * @return array|int[] Returns array of image IDs.
     */
    protected function upsertImages(array $imageIds, array $imageUrls, string $description = ''): array // phpcs:ignore
    {
        $existingImages = [];
        foreach ($imageIds as $imageId) {
            $source = get_field(Media::FIELD__ORIGINAL_SOURCE, $imageId) ?: '';
            if ($source) {
                $existingImages[$source] = $imageId;
            }
        }

        $outputImageIds = [];
        foreach ($imageUrls as $imageUrl) {
            try {
                $imageId = $existingImages[$imageUrl] ?? $this->createImageAttachmentFromUrl($imageUrl, $description);
                update_field(Media::FIELD__ORIGINAL_SOURCE, $imageUrl, $imageId);
                $outputImageIds[] = $imageId;
            } catch (Exception $exception) {
                $this->logger->warning(
                    'PPWORKS - Error uploading image.',
                    ['exception' => $exception->getMessage()]
                );
            }
        }

        return $outputImageIds;
    }

    /**
     * Upsert redirect.
     *
     * @param string $sourceUri
     * @param string $targetUri
     *
     * @return void
     * @throws Exception
     */
    protected function upsertRedirect(string $sourceUri, string $targetUri): void
    {
        if ($this->redirectionMissing()) {
            return;
        }
        $this->requireRedirectionFiles();

        Redirects::upsertRedirect($sourceUri, $targetUri);
    }

    /**
     * Is Redirection plugin missing?
     *
     * @return bool
     */
    private function redirectionMissing(): bool
    {
        return !defined('REDIRECTION_FILE') && !REDIRECTION_FILE;
    }

    /**
     * Require necessary Redirection files.
     *
     * @return void
     */
    private function requireRedirectionFiles(): void
    {
        // Thanks, WordPress.
        require_once plugin_dir_path(REDIRECTION_FILE) . 'models/group.php';
    }

    /**
     * Find redirect for the specified URI.
     *
     * @param string $uri
     *
     * @return Red_Item|null
     */
    /** @phpstan-ignore-next-line */
    private function findRedirect(string $uri): ?Red_Item
    {
        return Redirects::findRedirect($uri);
    }
}
