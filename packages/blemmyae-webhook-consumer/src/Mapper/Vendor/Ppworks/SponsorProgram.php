<?php

/**
 * @author  Konstantin Gusev <guvkon.net@icloud.com>
 * @license proprietary
 */

declare(strict_types=1);

namespace Cra\WebhookConsumer\Mapper\Vendor\Ppworks;

use Cra\BlemmyaePpworks\Ppworks;
use Cra\BlemmyaePpworks\PpworksSponsorProgramCT;
use Cra\Integrations\WebhookMessenger\ConsumerObjectId;
use Cra\Integrations\WebhookMessenger\EntityId;
use Cra\WebhookConsumer\Mapper\AbstractWordpressWebhookMapper;
use Cra\WebhookConsumer\Mapper\PostTrait;
use Cra\WebhookConsumer\WebhookMapping;
use Cra\WebhookConsumer\WebhookMessageHandler;
use Exception;
use Scm\Tools\WpCore;

/**
 * Webhook mapper for saving into ppworks sponsor program.
 */
class SponsorProgram extends AbstractWordpressWebhookMapper
{
    use PostTrait;

    public const TYPE = 'sponsor_program';

    /**
     * @inheritDoc
     */
    public function upsert(ConsumerObjectId $id, int $timestamp, object $object): EntityId
    {
        $postStatus = $this->objectIsActive() ?
            WpCore::POST_STATUS_PUBLISH : Ppworks::POST_STATUS__UNFINISHED;
        $this->postId = $this->upsertWebhookMappingAsPost(
            $this->webhookMappingWithFallback($id),
            $timestamp,
            [
                'post_title' => $this->objectName(),
                'post_status' => $postStatus,
            ]
        );
        $this->updateThisPostStatus($postStatus);
        $this->updateAcfFields();

        return $this->getThisPostEntityId();
    }

    /**
     * Update ACF fields of the post.
     *
     * @return void
     * @throws Exception
     */
    private function updateAcfFields(): void
    {
        $this->updateAcfField(
            PpworksSponsorProgramCT::GROUP_PPWORKS_SPONSOR_PROGRAM_ADVANCED__FIELD_SPONSOR,
            $this->objectSponsor()->postId
        );
        $this->updateAcfField(
            PpworksSponsorProgramCT::GROUP_PPWORKS_SPONSOR_PROGRAM_ADVANCED__FIELD_LANDING_PAGE_URL,
            $this->objectLandingPageUrl()
        );
        $this->updateAcfField(
            PpworksSponsorProgramCT::GROUP_PPWORKS_SPONSOR_PROGRAM_ADVANCED__FIELD_IS_ACTIVE,
            (int)$this->objectIsActive()
        );
        $this->updateAcfField(
            PpworksSponsorProgramCT::GROUP_PPWORKS_SPONSOR_PROGRAM_ADVANCED__FIELD_TIER,
            $this->objectTier()
        );
        $this->updateAcfField(
            PpworksSponsorProgramCT::GROUP_PPWORKS_SPONSOR_PROGRAM_ADVANCED__FIELD_BRAND,
            $this->objectBrand()
        );
        $this->updateAcfField(
            PpworksSponsorProgramCT::GROUP_PPWORKS_SPONSOR_PROGRAM_ADVANCED__FIELD_BRAND_ORDER,
            $this->objectBrandOrder()
        );
    }

    /**
     * Get object name.
     *
     * @return string
     */
    private function objectName(): string
    {
        return (string)($this->object->name ?? '');
    }

    /**
     * Get object sponsor.
     *
     * @return WebhookMapping
     * @throws Exception
     */
    private function objectSponsor(): WebhookMapping
    {
        $mapping = WebhookMapping::findById(
            new ConsumerObjectId(
                WebhookMessageHandler::VENDOR__PPWORKS,
                Sponsor::TYPE,
                (string)($this->object->sponsor ?? '')
            )
        );

        if (!$mapping) {
            throw new Exception('Missing sponsor mapping when saving sponsor program.');
        }

        return $mapping;
    }

    /**
     * Get object landing page URL.
     *
     * @return string
     */
    private function objectLandingPageUrl(): string
    {
        return (string)($this->object->landing_page_url ?? '');
    }

    /**
     * Get object is_active.
     *
     * @return bool
     */
    private function objectIsActive(): bool
    {
        return !empty($this->object->is_active);
    }

    /**
     * Get object tier.
     *
     * @return string
     */
    private function objectTier(): string
    {
        return (string)($this->object->tier ?? '');
    }

    /**
     * Get object brand.
     *
     * @return string
     */
    private function objectBrand(): string
    {
        return (string)($this->object->brand ?? '');
    }

    /**
     * Get object brand order.
     *
     * @return int
     */
    private function objectBrandOrder(): int
    {
        return (int)($this->object->brand_order ?? 0);
    }

    /**
     * @inheritDoc
     */
    public function wpEntityBundle(): string
    {
        return PpworksSponsorProgramCT::POST_TYPE;
    }
}
