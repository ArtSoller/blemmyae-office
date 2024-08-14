<?php

/**
 * @licence proprietary
 *
 * @author Konstantin Gusev <guvkon.net@icloud.com>
 */

declare(strict_types=1);

namespace Cra\BlemmyaeDeployment\A9SMigration;

use Cra\Integrations\WebhookMessenger\ConsumerMapping;
use Cra\WebhookConsumer\BlemmyaeWebhookConsumerStreamConnector;
use Exception;

/**
 * Entity class for webhook mappings.
 */
final class MigrationMapping extends ConsumerMapping
{
    public ?bool $triggerAction;

    /**
     * @inheritDoc
     */
    public static function getTableMapping(): string
    {
        $tableMapper = new MigrationMappingTable();
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
        $idPlaceholders = join(', ', array_fill(0, count($ids), '%d'));
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT `id`, `post_id`, `timestamp`, `entity_type` FROM $table
                    WHERE `vendor` = %s AND `type` = %s AND `id` IN ($idPlaceholders) LIMIT %d",
                $arguments
            )
        );
        if (empty($results) || count($results) !== count($ids)) {
            throw new Exception('Missing some or all webhook mapping IDs.');
        }

        // Make sure that we return MigrationMappings in the same order as received $ids.
        $dbObjectsById = [];
        foreach ($results as $dbObject) {
            $dbObjectsById[$dbObject->id] = $dbObject;
        }

        $migrationMappings = [];
        foreach ($ids as $id) {
            $dbObject = $dbObjectsById[$id] ?? null;
            if ($dbObject) {
                $dbObject->vendor = $vendor;
                $dbObject->type = $type;
                $migrationMappings[] = new MigrationMapping($dbObject);
            }
        }

        return $migrationMappings;
    }

    /**
     * Class constructor.
     *
     * @param ?object $dataObject
     */
    public function __construct(object $dataObject = null)
    {
        parent::__construct($dataObject);
        $this->triggerAction = false;
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

        if ($exists && $this->triggerAction) {
            do_action(
                BlemmyaeWebhookConsumerStreamConnector::ACTION_WEBHOOK_MESSAGE_MAPPING_UPDATED,
                $this->type,
                $this->postId
            );
        }
    }

    /**
     * @inheritDoc
     */
    public static function createConsumerMapping(object $dataObject): static
    {
        return new self($dataObject);
    }
}
