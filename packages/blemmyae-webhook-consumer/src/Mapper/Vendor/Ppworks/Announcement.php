<?php

/**
 * @author  Konstantin Gusev <guvkon.net@icloud.com>
 * @license proprietary
 */

declare(strict_types=1);

namespace Cra\WebhookConsumer\Mapper\Vendor\Ppworks;

use Cra\BlemmyaePpworks\PpworksAnnouncementCT;
use Cra\Integrations\WebhookMessenger\ConsumerObjectId;
use Cra\Integrations\WebhookMessenger\EntityId;
use Cra\WebhookConsumer\Mapper\AbstractWordpressWebhookMapper;
use Cra\WebhookConsumer\Mapper\MediaTrait;
use Cra\WebhookConsumer\Mapper\PostTrait;

/**
 * Webhook mapper for ppworks_announcement post type.
 */
class Announcement extends AbstractWordpressWebhookMapper
{
    use PostTrait;
    use MediaTrait;

    public const TYPE = 'announcement';

    /**
     * @inheritDoc
     */
    public function upsert(ConsumerObjectId $id, int $timestamp, object $object): EntityId
    {
        $this->postId = $this->upsertWebhookMappingAsPost(
            $this->webhookMappingWithFallback($id),
            $timestamp,
            [
                'post_title' => $this->objectTitle(),
                'post_content' => $this->objectDescription(),
            ]
        );

        $this->updateApplicationField();
        $this->updateImageField(
            $this->postId,
            $this->objectImage(),
            PpworksAnnouncementCT::GROUP_PPWORKS_ANNOUNCEMENT_ADVANCED__FIELD_FEATURED_IMAGE,
            "Featured image for {$this->objectTitle()} announcement from PPWorks"
        );

        return $this->getThisPostEntityId();
    }

    /**
     * Get object title.
     *
     * @return string
     */
    private function objectTitle(): string
    {
        return (string)($this->object->title ?? '');
    }

    /**
     * Get object description.
     *
     * @return string
     */
    private function objectDescription(): string
    {
        return (string)($this->object->description ?? '');
    }

    /**
     * Get object description.
     *
     * @return string
     */
    private function objectImage(): string
    {
        return (string)($this->object->image ?? '');
    }

    /**
     * @inheritDoc
     */
    public function wpEntityBundle(): string
    {
        return PpworksAnnouncementCT::POST_TYPE;
    }
}
