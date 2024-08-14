<?php

/**
 * @author  Konstantin Gusev <guvkon.net@icloud.com>
 * @license proprietary
 */

declare(strict_types=1);

namespace Cra\WebhookConsumer\Mapper\Vendor\Ppworks;

use Cra\BlemmyaePpworks\PpworksArticleCT;
use Cra\Integrations\WebhookMessenger\ConsumerObjectId;
use Cra\Integrations\WebhookMessenger\EntityId;
use Cra\WebhookConsumer\Mapper\AbstractWordpressWebhookMapper;
use Cra\WebhookConsumer\Mapper\PostTrait;
use Cra\WebhookConsumer\WebhookMapping;
use Cra\WebhookConsumer\WebhookMessageHandler;
use Scm\Tools\WpCore;

/**
 * Webhook mapper for ppworks_episode post type.
 */
class Article extends AbstractWordpressWebhookMapper
{
    use PostTrait;

    public const TYPE = 'article';

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
                'post_status' => WpCore::POST_STATUS_DRAFT,
            ]
        );

        $this->updateApplicationField();

        $this->updateAcfField(
            PpworksArticleCT::GROUP_PPWORKS_ARTICLE_ADVANCED__FIELD_DESCRIPTION,
            $this->objectBody()
        );

        $this->updateAcfField(
            PpworksArticleCT::GROUP_PPWORKS_ARTICLE_ADVANCED__FIELD_SOURCE_LINK,
            $this->objectSourceLink()
        );

        $this->updateAcfField(
            PpworksArticleCT::GROUP_PPWORKS_ARTICLE_ADVANCED__FIELD_POSITION,
            $this->objectPosition()
        );

        $host = $this->objectHost();
        $this->updateAcfField(
            PpworksArticleCT::GROUP_PPWORKS_ARTICLE_ADVANCED__FIELD_HOST,
            $host ? $host->postId : ''
        );

        $this->publishThisPost();

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
     * Get object position.
     *
     * @return WebhookMapping|null
     */
    private function objectHost(): ?WebhookMapping
    {
        if (empty($this->object->host)) {
            return null;
        }

        return WebhookMapping::findById(
            new ConsumerObjectId(
                WebhookMessageHandler::VENDOR__PPWORKS,
                Person::TYPE__HOST,
                (string)$this->object->host
            )
        );
    }

    /**
     * Get object position.
     *
     * @return int
     */
    private function objectPosition(): int
    {
        return (int)($this->object->position ?? 0);
    }

    /**
     * Get object body.
     *
     * @return string
     */
    private function objectBody(): string
    {
        return (string)($this->object->body ?? '');
    }

    /**
     * Get object source link.
     *
     * @return string
     */
    private function objectSourceLink(): string
    {
        return (string)($this->object->source_link ?? '');
    }

    /**
     * @inheritDoc
     */
    public function wpEntityBundle(): string
    {
        return PpworksArticleCT::POST_TYPE;
    }
}
