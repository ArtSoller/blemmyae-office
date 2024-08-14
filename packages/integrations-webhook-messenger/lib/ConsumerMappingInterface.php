<?php

/**
 * @licence proprietary
 *
 * @author  Eugene Yakovenko <yakoveka@gmail.com>
 */

declare(strict_types=1);

namespace Cra\Integrations\WebhookMessenger;

use Exception;
use Generator;

/**
 * Interfaces for webhook mapper classes which save webhook object to the target system.
 */
interface ConsumerMappingInterface
{
    /**
     * Delete entry by entity ID and type.
     *
     * @param int|string $entityId
     * @param string $entityType Possible values: "post", "term".
     *
     * @return void
     * @throws Exception
     */
    public static function deleteByEntityIdAndType(int|string $entityId, string $entityType): void;

    /**
     * Delete entries by vendor and type.
     *
     * @param string $vendor
     * @param string $type
     *
     * @return void
     * @throws Exception
     */
    public static function deleteByVendorAndType(string $vendor, string $type): void;

    /**
     * Find webhook mapping by webhook object ID.
     *
     * @param ConsumerObjectId $id
     * @param bool $withObject
     *
     * @return ?static
     */
    public static function findById(ConsumerObjectId $id, bool $withObject = false): ?static;

    /**
     * Find multiple webhook mappings by IDs for type and vendor.
     *
     * @param string[]|int[] $ids
     * @param string $type
     * @param string $vendor
     * @param bool $throw Throw an Exception if some of the IDs are missing. Defaults to true.
     *
     * @return array|ConsumerMappingInterface[]
     * @throws Exception
     */
    public static function findMultiple(array $ids, string $type, string $vendor, bool $throw = true): array;

    /**
     * Upsert webhook mapping to DB.
     */
    public function upsert(): void;

    /**
     * Find multiple webhook mappings by
     *
     * @param string $vendor
     * @param array<string> $types
     * @param bool $withObject
     *
     * @return Generator<ConsumerMappingInterface>
     */
    public static function findByVendorAndType(string $vendor, array $types, bool $withObject = false): Generator;
}
