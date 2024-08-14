<?php

/**
 * @author  Konstantin Gusev <konstantin.gusev@cyberriskalliance.com>
 * @license proprietary
 */

namespace Cra\WebhookConsumer\Mapper;

use Cra\BlemmyaeApplications\BlemmyaeApplications;
use Cra\BlemmyaeApplications\CerberusApps;
use Cra\BlemmyaeApplications\Entity\Term;
use Cra\Integrations\WebhookMessenger\EntityId;
use Cra\WebhookConsumer\BlemmyaeWebhookConsumerStreamConnector;
use Cra\WebhookConsumer\Logger;
use Cra\WebhookConsumer\Webhook;
use Cra\WebhookConsumer\WebhookMapping;
use DateTimeZone;
use Exception;
use Scm\Tools\WpCore;
use Scm\WP_GraphQL\Options;
use WP_Post;
use WP_Query;

/**
 * Trait to be used by mappers for posts.
 */
trait PostTrait
{
    use AcfTrait;

    /**
     * Get entity ID expected by the webhook consumer.
     *
     * @return EntityId
     */
    protected function getThisPostEntityId(): EntityId
    {
        return new EntityId($this->postId, 'post');
    }

    /**
     * Get vendor ID.
     *
     * @return string|int
     */
    abstract protected function vendorId(): string|int;

    /**
     * Get post type.
     *
     * @return string
     */
    abstract public function wpEntityBundle(): string;

    /**
     * Upsert post.
     *
     * @param WebhookMapping $webhookMapping
     * @param int $timestamp Timestamp in milliseconds.
     * @param array{
     *              'ID'?: ?int,
     *              'post_date'?: ?string,
     *              'post_content'?: ?string,
     *              'post_title'?: ?string,
     *              'post_name'?: ?string,
     *              'post_status'?: ?string,
     *          } $postarr Post data array as defined by wp_insert_post().
     *
     * @return int Returns post ID.
     * @throws Exception
     * @see \wp_insert_post()
     * @see \wp_upsert_post()
     */
    protected function upsertWebhookMappingAsPost(WebhookMapping $webhookMapping, int $timestamp, array $postarr): int
    {
        $postarr += [
            'ID' => $webhookMapping->postId,
            'post_status' => WpCore::POST_STATUS_PUBLISH,
            'post_type' => $this->wpEntityBundle(),
        ];
        if (!empty($postarr['ID'])) {
            // Do not change status of a post if it's already in DB.
            unset($postarr['post_status']);
        } elseif (empty($postarr['post_date_gmt']) && empty($postarr['post_date'])) {
            // For new posts set date to the timestamp if not manually set.
            $postarr['post_date'] = date_create('now', wp_timezone())
                ->setTimestamp((int)($timestamp / 1000))
                ->format('c');
        }

        return $this->upsertAnyPost($postarr, Webhook::messageKey($webhookMapping->webhookObjectId()));
    }

    /**
     * Upsert any WP post.
     *
     * @param array{
     *             'ID'?: ?int,
     *             'post_date'?: ?string,
     *             'post_content'?: ?string,
     *             'post_title'?: ?string,
     *             'post_name'?: ?string,
     *             'post_status'?: ?string,
     *             'post_type'?: ?string,
     *         } $postarr Post data array as defined by wp_insert_post() and wp_update_post()
     * @param string $mappingKey Optional.
     *
     * @return int
     * @throws Exception
     * @see \wp_insert_post()
     * @see \wp_update_post()
     */
    protected function upsertAnyPost(array $postarr, string $mappingKey = ''): int
    {
        $postExists = !empty($postarr['ID']);
        if (!array_key_exists('post_name', $postarr)) {
            $postarr['post_name'] = $this->generateUniquePostSlug(
                $postarr['post_title'] ?? '',
                $postarr['ID'] ?? null
            );
        }

        $post = $postExists ? WpCore::updatePost($postarr) : WpCore::insertPost($postarr);

        // Update application slug - based on post_name.
        // Must load post to use post_name that actually has been saved.
        $this->updateApplicationSlugField($post->ID, $post->post_name);

        $hook = $postExists ?
            BlemmyaeWebhookConsumerStreamConnector::ACTION_WEBHOOK_MESSAGE_POST_UPDATED :
            BlemmyaeWebhookConsumerStreamConnector::ACTION_WEBHOOK_MESSAGE_POST_CREATED;
        do_action(
            $hook,
            $mappingKey,
            $post->ID
        );

        return (int)$post->ID;
    }

    /**
     * Sets|Updates 'Application Slug' ACF field.
     *
     * @param int|string $postId
     *  Post ID.
     * @param string $slug
     *  Slug of the application. With empty slug system will use post_name as application slug.
     *
     * @return void
     */
    private function updateApplicationSlugField(int|string $postId, string $slug = ''): void
    {
        update_field(
            CerberusApps::APPLICATION_SLUG_FIELD,
            $slug,
            $postId
        );
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
        $slug .= '-' . $this->vendorId();
        if ($this->isPostSlugUnique($slug, $postId)) {
            return $slug;
        }
        throw new Exception("Cannot create a unique slug for $title");
    }

    /**
     * Check if a post slug is unique.
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
            'post_status' => array_merge(Options::getPublicPostStatuses(), ['future']),
        ];
        if ($postId) {
            $args['post__not_in'] = [$postId];
        }
        $query = new WP_Query($args);

        return $query->post_count === 0;
    }

    /**
     * Sets|Updates 'Application' ACF field.
     *
     * @param string $applicationSlug
     *  Slug of the application. By default, we use SCM.
     *
     * @return void
     */
    protected function updateApplicationField(string $applicationSlug = BlemmyaeApplications::SCM): void
    {
        // @todo by default we select SCM application in the 1st iteration, then we need to update the logic.
        // @todo check that application exists, if not => send error into logs.
        $this->updateAcfField(
            CerberusApps::APPLICATION_FIELD,
            Term::getAppTermIdByAppSlug($applicationSlug),
        );
    }

    /**
     * Get post associated with the mapper class instance.
     *
     * @return WP_Post
     * @throws Exception
     */
    protected function getThisPost(): WP_Post
    {
        return WpCore::getPost($this->postId);
    }

    /**
     * Publish post associated with the mapper class instance.
     *
     * If post is already published then nothing happens.
     *
     * @return void
     */
    protected function publishThisPost(): void
    {
        wp_publish_post($this->postId);
    }

    /**
     * Publish post associated with the mapper class instance if its status is 'draft'.
     *
     * It's very important that any WP post becomes published after draft before
     * setting any other custom status.
     *
     * @return bool
     * @throws Exception
     */
    protected function publishThisDraftPost(): bool
    {
        if ($this->getThisPost()->post_status === WpCore::POST_STATUS_DRAFT) {
            $this->publishThisPost();
            return true;
        }
        return false;
    }

    /**
     * Update status of the post associated with the mapper class instance.
     *
     * It's only updated if it's not already set to that status.
     *
     * @param string $status
     *
     * @return void
     * @throws Exception
     */
    protected function updateThisPostStatus(string $status): void
    {
        if ($this->getThisPost()->post_status !== $status) {
            WpCore::updatePostStatus($this->postId, $status);
        }
    }

    /**
     * Clean up (delete) post associated with the mapper class instance.
     *
     * @return void
     * @throws Exception
     */
    protected function cleanupThisPost(): void
    {
        try {
            if ($this->getThisPost()->post_status === WpCore::POST_STATUS_DRAFT) {
                WpCore::deletePost($this->postId, true);
            }
        } catch (Exception $exception) {
            (new Logger())->warning("Unable to delete post during clean up: ID = $this->postId - $exception");
        }
    }
}
