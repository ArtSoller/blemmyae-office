<?php

/**
 * @author  Konstantin Gusev <konstantin.gusev@cyberriskalliance.com>
 * @license proprietary
 */

namespace Cra\WebhookConsumer\Mapper;

use Cra\Integrations\WebhookMessenger\EntityId;
use Cra\WebhookConsumer\WebhookMapping;
use Exception;
use Scm\Tools\WpCore;

/**
 * Trait to be used by mappers for taxonomy terms.
 */
trait TaxonomyTrait
{
    protected int $termId;

    /**
     * Get entity ID expected by the webhook consumer.
     *
     * @return EntityId
     */
    protected function getThisTermEntityId(): EntityId
    {
        return new EntityId($this->termId, 'term');
    }

    /**
     * Get taxonomy name.
     *
     * @return string
     */
    abstract public function wpEntityBundle(): string;

    /**
     * Upsert taxonomy term.
     *
     * @param WebhookMapping $webhookMapping
     * @param string $name
     *
     * @return int Returns taxonomy term ID.
     * @throws Exception
     */
    protected function upsertWebhookMappingAsTaxonomyTerm(WebhookMapping $webhookMapping, string $name): int
    {
        return $webhookMapping->postId ?:
            WpCore::getTermByName($this->wpEntityBundle(), $name, true)->term_id;
    }
}
