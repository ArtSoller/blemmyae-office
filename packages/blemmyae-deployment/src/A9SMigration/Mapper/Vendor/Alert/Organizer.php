<?php

/**
 * @author  Konstantin Gusev <guvkon.net@icloud.com>
 * @license proprietary
 */

declare(strict_types=1);

namespace Cra\BlemmyaeDeployment\A9SMigration\Mapper\Vendor\Alert;

use Cra\BlemmyaeDeployment\A9SMigration\AbstractPostMigrationMapper;
use Cra\CtCompanyProfile\CompanyProfileCT;
use Cra\Integrations\WebhookMessenger\ConsumerObjectId;
use Cra\Integrations\WebhookMessenger\EntityId;
use Cra\WebhookConsumer\Logger;
use Exception;
use WP_Query;

/**
 * Webhook mapper for saving into company content type.
 */
class Organizer extends AbstractPostMigrationMapper
{
    public const TYPE = 'organizer';

    public const INTERNAL_TAXONOMY_TYPE = 'Vendor';

    /**
     * @inheritDoc
     */
    public function upsert(ConsumerObjectId $id, int $timestamp, object $object): EntityId
    {
        $postId = $this->upsertCompanyPost($id, $timestamp);
        try {
            $this->updateTermsOnTaxonomyField(
                $postId,
                CompanyProfileCT::GROUP_COMPANY_PROFILE_TAXONOMY__FIELD_TYPE,
                CompanyProfileCT::TAXONOMY__COMPANY_PROFILE_TYPE,
                [$this::INTERNAL_TAXONOMY_TYPE]
            );
            $this->updateCompanyAcfFields($postId);
        } catch (Exception $exception) {
            (new Logger())->warning(
                sprintf(
                    'Unable to update ACF fields for Company Profile post %s: %s',
                    $postId,
                    $exception->getMessage()
                )
            );
            wp_publish_post($postId);

            return new EntityId($postId, 'post');
        }
        wp_publish_post($postId);

        return new EntityId($postId, 'post');
    }

    /**
     * @inheritDoc
     */
    public function wpEntityBundle(): string
    {
        return CompanyProfileCT::POST_TYPE;
    }

    /**
     * Updates|Inserts 'Company Profile' post type post.
     *
     * @param ConsumerObjectId $id
     * @param int $timestamp
     *
     * @return int  Post ID.
     * @throws Exception
     */
    protected function upsertCompanyPost(ConsumerObjectId $id, int $timestamp): int
    {
        $migrationMapping = $this->migrationMapping($id);
        $postId = $migrationMapping->postId ?? 0;
        if (!$postId) {
            $postId = $this->findCompanyPostId();
            if (!$postId) {
                $postId = $this->upsertPost($id, $timestamp, [
                    'post_title' => $this->objectName(),
                    'post_status' => 'draft',
                ]);
            }
        }

        return $postId;
    }

    /**
     * Finds current company's post ID.
     * Found if 'Company Profile' post with the same name is published.
     *
     * @return int 'Company Profile' post type post ID or 0 if not found.
     */
    protected function findCompanyPostId(): int
    {
        $query = new WP_Query([
            'post_status' => 'publish',
            'post_type' => $this->wpEntityBundle(),
            'posts_per_page' => 1,
            'meta_query' => [
                [
                    'key' => 'company_name',
                    'value' => $this->objectName(),
                ],
            ],
        ]);

        $post = $query->have_posts() ? $query->next_post() : null;

        return $post ? (int)$post->ID : 0;
    }

    /**
     * Updates 'Company Profile Advanced' ACF field group fields.
     *
     * @param int $postId
     *
     * @return $this
     */
    protected function updateCompanyAcfFields(int $postId): self
    {
        if ($this->objectName()) {
            update_field(
                CompanyProfileCT::GROUP_COMPANY_PROFILE_ADVANCED__FIELD_COMPANY_NAME,
                $this->objectName(),
                $postId
            );
        }

        if ($this->objectDescription()) {
            update_field(
                CompanyProfileCT::GROUP_COMPANY_PROFILE_ADVANCED__FIELD_ABOUT,
                $this->objectDescription(),
                $postId
            );
        }

        if ($this->objectWebsite()) {
            update_field(
                CompanyProfileCT::GROUP_COMPANY_PROFILE_ADVANCED__FIELD_WEBSITE_URL,
                $this->objectWebsite(),
                $postId
            );
        }

        if ($this->objectEmail()) {
            update_field(
                CompanyProfileCT::GROUP_COMPANY_PROFILE_ADVANCED__FIELD_EMAIL,
                $this->objectEmail(),
                $postId
            );
        }

        # @fixme: Upon update country value is not populated.
        $phone = preg_replace("/\D+/", '', $this->objectPhone());
        if (strlen($phone) > 6) {
            update_field(
                CompanyProfileCT::GROUP_COMPANY_PROFILE_ADVANCED__FIELD_PHONE,
                $phone,
                $postId
            );
        }

        return $this;
    }

    /**
     * @return string
     */
    protected function objectName(): string
    {
        return (string)$this->object->post->post_title;
    }

    /**
     * @return string
     */
    protected function objectDescription(): string
    {
        return (string)$this->object->post->post_content;
    }

    /**
     * @return string
     */
    protected function objectAuthor(): string
    {
        return (string)$this->object->post->post_author;
    }

    /**
     * @return string
     */
    protected function objectWebsite(): string
    {
        return (string)($this->object->meta["_OrganizerWebsite"][0] ?? '');
    }

    /**
     * @return string
     */
    protected function objectOrigin(): string
    {
        return (string)($this->object->meta["_OrganizerOrigin"][0] ?? '');
    }

    /**
     * @return string
     */
    protected function objectOrganizerID(): string
    {
        return (string)($this->object->meta["_OrganizerOrganizerID"][0] ?? '');
    }

    /**
     * @return string
     */
    protected function objectPhone(): string
    {
        return (string)($this->object->meta["_OrganizerPhone"][0] ?? '');
    }

    /**
     * @return string
     */
    protected function objectEmail(): string
    {
        return (string)($this->object->meta["_OrganizerEmail"][0] ?? '');
    }
}
