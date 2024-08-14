<?php

/**
 * @author  Nikita Sokolskiy <n_sokolskiy@dotwrk.com>
 * @license proprietary
 */

declare(strict_types=1);

namespace Cra\WebhookConsumer\Mapper\Vendor\Convertr;

use Cra\CtWhitepaper\Whitepaper as CtWhitepaper;
use Cra\CtWhitepaper\WhitepaperCT;
use Cra\Integrations\WebhookMessenger\ConsumerObjectId;
use Cra\Integrations\WebhookMessenger\EntityId;
use Cra\WebhookConsumer\Mapper\AbstractWordpressWebhookMapper;
use Cra\WebhookConsumer\Mapper\MediaTrait;
use Cra\WebhookConsumer\Mapper\PostTrait;
use Scm\Tools\Logger;
use Scm\Tools\WpCore;

/**
 * Webhook mapper for whitepaper post type.
 */
class Whitepaper extends AbstractWordpressWebhookMapper
{
    use PostTrait;
    use MediaTrait;

    public const TYPE = 'whitepaper';

    /**
     * @inheritDoc
     */
    public function wpEntityBundle(): string
    {
        return WhitepaperCT::POST_TYPE;
    }

    /**
     * @inheritDoc
     */
    public function upsert(ConsumerObjectId $id, int $timestamp, object $object): EntityId
    {
        $log = fn (string $message) => Logger::log(
            sprintf(
                "[Convertr Sync, whitepaper id %s from campaign %s] %s",
                $this->objectWhitepaperId(),
                $this->objectCampaignId(),
                $message
            ),
            'info'
        );

        $log('Upsert whitepaper');
        $this->postId = $this->upsertWebhookMappingAsPost(
            $this->webhookMappingWithFallback($id),
            $timestamp,
            [
                'post_title' => $this->objectTitle(),
                'post_status' => WpCore::POST_STATUS_DRAFT,
            ] + ($this->objectDescription() ? [
                'post_content' => $this->objectDescription(),
            ] : []),
        );

        if ($this->isConvertr()) {
            $log("Convertr whitepaper, updating vendor field, post id: $this->postId");
            $this->updateAcfField(
                WhitepaperCT::GROUP_WHITEPAPER_ADVANCED__FIELD_VENDOR,
                [
                    [
                        'acf_fc_layout' => WhitepaperCT::VENDOR__CONVERTR,
                        'campaign_id' => $object->campaignId ?? '',
                        'campaign_api_key' => $object->campaignApiKey ?? '',
                        'link_id' => $object->linkId ?? '',
                        'download_link' => $object->downloadLink ?? '',
                        'whitepaper_id' => $object->whitepaperId ?? '',
                        'form_id' => $object->formId ?? '',
                        'form_fields' => $object->formFields ?? '',
                    ],
                ]
            );
        }

        if ($this->isInternal()) {
            $log("Internal whitepaper, updating vendor field, post id: $this->postId");
            /** @phpstan-ignore-next-line */
            $fileAttachment = get_field(CtWhitepaper::FIELD_WHITEPAPER_FILE_ATTACHMENT, $this->postId);
            $fileAttachmentId = $fileAttachment ? (int)$fileAttachment['ID'] : null;
            $newAttachmentIds = $this->upsertImages([$this->objectFileAttachment()]);
            $newAttachmentId = $newAttachmentIds[0] ?? null;
            if ($newAttachmentId !== $fileAttachmentId) {
                $this->updateAcfField(
                    WhitepaperCT::GROUP_WHITEPAPER_ADVANCED__FIELD_VENDOR,
                    [
                        [
                            'acf_fc_layout' =>
                                WhitepaperCT::VENDOR__INTERNAL_WHITEPAPER,
                            'file' => $newAttachmentId ?? '',
                        ],
                    ]
                );
            }
        }

        $log("Updating featured image field, post id: $this->postId");
        $this->updateImageField(
            $this->postId,
            $this->objectImage(),
            WhitepaperCT::GROUP_WHITEPAPER_ADVANCED__FIELD_FEATURED_IMAGE,
            "Featured image for {$this->objectTitle()} convertr whitepaper"
        );

        $log("Updating application field, post id: $this->postId");
        $this->updateApplicationField();

        $this->publishThisPost();

        $log("Returning $this->postId from upsert function");

        return $this->getThisPostEntityId();
    }

    /**
     * Get object title.
     *
     * @return string
     */
    private function objectTitle(): string
    {
        return (string)($this->object->humanName ?? '');
    }

    /**
     * Get object description.
     *
     * @return string
     */
    private function objectDescription(): ?string
    {
        return (string)($this->object->excerpt ?? null);
    }

    /**
     * Get object description.
     *
     * @return string
     */
    private function objectImage(): string
    {
        return (string)($this->object->coverLink ?? '');
    }

    /**
     * Get object description.
     *
     * @return string
     */
    private function objectFileAttachment(): string
    {
        return isset($this->object->downloadLink) ? "{$this->object->downloadLink}.pdf" : '';
    }

    private function isInternal(): bool
    {
        return !empty($this->object->internal);
    }

    private function isConvertr(): bool
    {
        return !$this->isInternal();
    }

    /**
     * @return string
     */
    private function objectWhitepaperId(): string
    {
        return (string)($this->object->whitepaperId ?? '');
    }

    /**
     * @return string
     */
    private function objectCampaignId(): string
    {
        return (string)($this->object->campaignId ?? '');
    }
}
