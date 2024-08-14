<?php

/**
 * @author  Konstantin Gusev <guvkon.net@icloud.com>
 * @license proprietary
 */

declare(strict_types=1);

namespace Cra\WebhookConsumer\Mapper\Vendor\Ppworks;

use Cra\BlemmyaePpworks\PpworksEpisodeCT;
use Cra\Integrations\WebhookMessenger\ConsumerObjectId;
use Cra\Integrations\WebhookMessenger\EntityId;
use Cra\WebhookConsumer\Mapper\AbstractWordpressWebhookMapper;
use Cra\WebhookConsumer\Mapper\TaxonomyTrait;

/**
 * Webhook mapper for ppworks_tag taxonomy term type.
 */
class Tag extends AbstractWordpressWebhookMapper
{
    use TaxonomyTrait;

    public const TYPE = 'tag';

    /**
     * @inheritDoc
     */
    public function upsert(ConsumerObjectId $id, int $timestamp, object $object): EntityId
    {
        $this->termId = $this->upsertWebhookMappingAsTaxonomyTerm(
            $this->webhookMappingWithFallback($id),
            $this->objectTitle()
        );
        return $this->getThisTermEntityId();
    }

    /**
     * Get object title.
     *
     * @return string
     */
    private function objectTitle(): string
    {
        $title = $this->object->title ?? '';

        return (string)$title;
    }

    /**
     * @inheritDoc
     */
    public function wpEntityBundle(): string
    {
        return PpworksEpisodeCT::TAXONOMY__TAG;
    }
}
