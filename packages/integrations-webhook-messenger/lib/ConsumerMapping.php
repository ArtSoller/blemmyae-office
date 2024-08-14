<?php

/**
 * @licence proprietary
 *
 * @author Eugene Yakovenko <yakoveka@gmail.com>
 */

declare(strict_types=1);

namespace Cra\Integrations\WebhookMessenger;

use Exception;
use Generator;
use stdClass;

/**
 * Entity class for consumer mappings.
 */
abstract class ConsumerMapping implements ConsumerMappingInterface
{
    public int $postId;

    public string $vendor;

    public string $type;

    public string|int $id;

    public int $timestamp;

    public string $entityType;

    public ?object $object;

    /**
     * @return string
     */
    abstract public static function getTableMapping(): string;

    /**
     * @inheritDoc
     */
    public static function deleteByEntityIdAndType(int|string $entityId, string $entityType): void
    {
        global $wpdb;

        $table = static::getTableMapping();
        $rowsUpdated = $wpdb->delete(
            $table,
            [
                'post_id' => $entityId,
                'entity_type' => $entityType,
            ],
            ['%d', '%s'],
        );
        if ($rowsUpdated === false) {
            throw new Exception(
                "There has been an error deleting from $table. ID = $entityId, type = $entityType"
            );
        }
    }

    /**
     * @inheritDoc
     */
    public static function deleteByVendorAndType(string $vendor, string $type): void
    {
        global $wpdb;

        $table = static::getTableMapping();
        $rowsUpdated = $wpdb->delete(
            $table,
            [
                'vendor' => $vendor,
                'type' => $type,
            ],
            ['%s', '%s'],
        );
        if ($rowsUpdated === false) {
            throw new Exception(
                "There has been an error deleting from $table. vendor = $vendor, type = $type"
            );
        }
    }

    /**
     * @inheritDoc
     */
    public static function findById(ConsumerObjectId $id, bool $withObject = false): ?static
    {
        global $wpdb;

        $table = static::getTableMapping();
        $extraFields = $withObject ? ', `object`' : '';
        /** @var null|array<stdClass&object{post_id: int, timestamp: int, entity_type: string, object?: string}> $results */
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT `post_id`, `timestamp`, `entity_type`$extraFields FROM $table
                    WHERE `vendor` = %s AND `type` = %s AND `id` = %s LIMIT 1",
                $id->getVendor(),
                $id->getType(),
                $id->getId()
            )
        );
        if (empty($results)) {
            return null;
        }

        $dataObject = $results[0];
        $dataObject->vendor = $id->getVendor();
        $dataObject->type = $id->getType();
        $dataObject->id = $id->getId();

        return static::createConsumerMapping($dataObject);
    }

    /**
     * @param object $dataObject
     *
     * @return $this
     */
    abstract public static function createConsumerMapping(object $dataObject): static;

    /**
     * @inheritDoc
     */
    abstract public static function findMultiple(array $ids, string $type, string $vendor, bool $throw = true): array;

    /**
     * @inheritDoc
     */
    public static function findByVendorAndType(string $vendor, array $types, bool $withObject = false): Generator
    {
        if (empty($vendor) || empty($types)) {
            return;
        }

        global $wpdb;

        $table = static::getTableMapping();
        $arguments = array_merge([$vendor], $types);
        $typePlaceholders = join(', ', array_fill(0, count($types), '%s'));
        $extraFields = $withObject ? ', `object`' : '';
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT `id`, `post_id`, `timestamp`, `type`, `entity_type`$extraFields FROM $table
                    WHERE `vendor` = %s AND `type` IN ($typePlaceholders)",
                $arguments
            )
        );

        foreach ($results as $dbObject) {
            $dbObject->vendor = $vendor;
            yield static::createConsumerMapping($dbObject);
        }
    }

    /**
     * Class constructor.
     *
     * @param ?object $dataObject
     */
    public function __construct(?object $dataObject)
    {
        if ($dataObject instanceof ConsumerObjectId) {
            $this->vendor = $dataObject->getVendor();
            $this->type = $dataObject->getType();
            $this->id = $dataObject->getId();
            $this->postId = 0;
            $this->timestamp = 0;
            $this->entityType = '';
            $this->object = null;
        } elseif ($dataObject) {
            /** @var stdClass&object{
             *     vendor: string,
             *     type: string,
             *     id: string,
             *     post_id: int,
             *     timestamp: int,
             *     entity_type: string,
             *     object: string
             * } $dataObject
             */
            $this->vendor = $dataObject->vendor;
            $this->type = $dataObject->type;
            $this->id = $dataObject->id;
            $this->postId = (int)$dataObject->post_id;
            $this->timestamp = (int)$dataObject->timestamp;
            $this->entityType = $dataObject->entity_type;
            $this->object = !empty($dataObject->object) ? json_decode($dataObject->object) : null;
        }
    }

    /**
     * @inheritDoc
     */
    abstract public function upsert(): void;
}
