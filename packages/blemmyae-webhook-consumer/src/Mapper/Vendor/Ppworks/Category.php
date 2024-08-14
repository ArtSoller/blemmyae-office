<?php

/**
 * @author  Konstantin Gusev <guvkon.net@icloud.com>
 * @license proprietary
 */

declare(strict_types=1);

namespace Cra\WebhookConsumer\Mapper\Vendor\Ppworks;

use Cra\Integrations\WebhookMessenger\ConsumerObjectId;
use Cra\Integrations\WebhookMessenger\EntityId;
use Cra\WebhookConsumer\Mapper\AbstractWordpressWebhookMapper;
use Exception;
use WP_Term;

/**
 * Webhook mapper for ppworks_show taxonomy term type.
 */
class Category extends AbstractWordpressWebhookMapper
{
    public const TYPE = 'category';

    /**
     * @inheritDoc
     */
    public function upsert(ConsumerObjectId $id, int $timestamp, object $object): EntityId
    {
        return new EntityId($this->findTopicTermId(), 'term');
    }

    /**
     * Find 'topic' taxonomy term ID.
     *
     * @return int
     * @throws Exception
     */
    private function findTopicTermId(): int
    {
        // get_term_by is case-insensitive on remote and local so there is some leeway in target mappings.
        $term = get_term_by(
            'name',
            $this->getTopic(),
            $this->wpEntityBundle(),
        );
        if (!($term instanceof WP_Term)) {
            throw new Exception(
                'Cannot find corresponding topic for ppworks category: ' . $this->objectTitle()
            );
        }

        return (int)$term->term_id;
    }

    /**
     * Get the corresponding topic name from object title.
     *
     * @return string
     * @throws Exception
     */
    private function getTopic(): string
    {
        return trim($this->objectTitle());
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
        return 'topic';
    }

    /**
     * @inheritDoc
     */
    protected function allowWpEntityDeletion(): bool
    {
        return false;
    }
}
