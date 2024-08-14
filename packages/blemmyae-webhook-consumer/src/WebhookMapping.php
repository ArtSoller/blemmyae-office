<?php

/**
 * @licence proprietary
 *
 * @author Konstantin Gusev <guvkon.net@icloud.com>
 * @author Eugene Yakovenko <yakoveka@gmail.com>
 */

declare(strict_types=1);

namespace Cra\WebhookConsumer;

use Cra\Integrations\WebhookMessenger\ConsumerMapping;
use Cra\Integrations\WebhookMessenger\ConsumerObjectId;
use Exception;
use stdClass;

/**
 * Entity class for webhook mappings.
 */
final class WebhookMapping extends ConsumerMapping
{
    /**
     * @inheritDoc
     */
    public static function getTableMapping(): string
    {
        $tableMapper = new WebhookMappingTable();
        return $tableMapper->getMappingsTable();
    }

    /**
     * @inheritDoc
     */
    public static function findMultiple(array $ids, string $type, string $vendor, bool $throw = true): array
    {
        if (empty($ids)) {
            return [];
        }

        global $wpdb;

        $table = self::getTableMapping();
        $arguments = array_merge([$vendor, $type], $ids);
        $arguments[] = count($ids);
        $idPlaceholders = join(', ', array_fill(0, count($ids), '%s'));
        /** @var null|array<stdClass&object{id: int, post_id: int, timestamp: int, entity_type: string}> $results */
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT `id`, `post_id`, `timestamp`, `entity_type` FROM $table
                    WHERE `vendor` = %s AND `type` = %s AND `id` IN ($idPlaceholders) LIMIT %d",
                $arguments
            )
        ) ?? [];
        if (!is_array($results)) {
            throw new Exception("Unexpected result returned inside WebhookMapping::findMultiple()");
        }

        if ($throw && count($results) !== count($ids)) {
            $_ids = join(',', $ids);
            throw new Exception("Missing some or all webhook mapping IDs. vendor=$vendor, type=$type, ids=$_ids");
        }

        // Make sure that we return WebhookMappings in the same order as received $ids.
        $dbObjectsById = [];
        foreach ($results as $dbObject) {
            $dbObjectsById[$dbObject->id] = $dbObject;
        }

        $webhookMappings = [];
        foreach ($ids as $id) {
            $dbObject = $dbObjectsById[$id] ?? null;
            if ($dbObject) {
                $dbObject->vendor = $vendor;
                $dbObject->type = $type;
                $webhookMappings[] = new WebhookMapping($dbObject);
            }
        }

        return $webhookMappings;
    }

    /**
     * Class constructor.
     *
     * @param ?object $dataObject
     */
    public function __construct(object $dataObject = null)
    {
        parent::__construct($dataObject);
    }

    /**
     * @inheritDoc
     */
    public function upsert(): void
    {
        global $wpdb;

        $table = $this->getTableMapping();
        $exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT 1 FROM $table
                    WHERE `vendor` = %s AND `type` = %s AND `id` = %s LIMIT 1",
                $this->vendor,
                $this->type,
                $this->id
            )
        );
        $data = [
            'post_id' => $this->postId,
            'vendor' => $this->vendor,
            'type' => $this->type,
            'id' => $this->id,
            'timestamp' => $this->timestamp,
            'object' => $this->object ? json_encode($this->object) : null,
            'entity_type' => $this->entityType,
        ];

        $mappingKey = implode('-', [$this->type, $this->vendor, $this->id]);

        $format = ['%d', '%s', '%s', '%s', '%d', '%s', '%s'];
        $exists ?
            $wpdb->update(
                $table,
                $data,
                [
                    'vendor' => $this->vendor,
                    'type' => $this->type,
                    'id' => $this->id,
                ],
                $format
            ) :
            $wpdb->insert($table, $data, $format);
        if ($exists) {
            do_action(
                BlemmyaeWebhookConsumerStreamConnector::ACTION_WEBHOOK_MESSAGE_MAPPING_UPDATED,
                $mappingKey,
                $this->postId
            );
            return;
        }
        do_action(
            BlemmyaeWebhookConsumerStreamConnector::ACTION_WEBHOOK_MESSAGE_MAPPING_CREATED,
            $mappingKey,
            $this->postId
        );
    }

    /**
     * Get ConsumerObjectId equivalent.
     *
     * @return ConsumerObjectId
     */
    public function webhookObjectId(): ConsumerObjectId
    {
        return new ConsumerObjectId(
            $this->vendor,
            $this->type,
            $this->id,
        );
    }

    /**
     * @inheritDoc
     */
    public static function createConsumerMapping(object $dataObject): static
    {
        return new self($dataObject);
    }
}
