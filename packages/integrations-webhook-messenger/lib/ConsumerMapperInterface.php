<?php

/**
 * @licence proprietary
 *
 * @author  Eugene Yakovenko <yakoveka@gmail.com>
 */

declare(strict_types=1);

namespace Cra\Integrations\WebhookMessenger;

use Exception;

/**
 * Interfaces for webhook mapper classes which save webhook object to the target system.
 */
interface ConsumerMapperInterface
{
    /**
     * Check if the latest version of the object is already present based on $id and $timestamp.
     *
     * @param ConsumerObjectId $id Object ID
     * @param int $timestamp Object modification timestamp
     *
     * @return bool Returns true if the existing object has greater timestamp
     */
    public function isObjectUptoDate(ConsumerObjectId $id, int $timestamp): bool;

    /**
     * Create object.
     *
     * ID and timestamp are to be preserved. Timestamp must be a separate field on the
     * target system which is only updated via webhook messages.
     *
     * @param ConsumerObjectId $id
     * @param int $timestamp
     * @param object $object
     *
     * @throws Exception
     */
    public function create(ConsumerObjectId $id, int $timestamp, object $object): void;

    /**
     * Update object.
     *
     * $object is always a complete version so missing fields compared to the existing
     * one are to be considered empty (default if applicable) values.
     *
     * @param ConsumerObjectId $id
     * @param int $timestamp
     * @param object $object
     *
     * @throws Exception
     */
    public function update(ConsumerObjectId $id, int $timestamp, object $object): void;

    /**
     * Delete object.
     *
     * Must keep an empty record of the object with $id and $timestamp intact.
     * It is necessary in case a create/update event comes later than it should.
     *
     * @param ConsumerObjectId $id
     * @param int $timestamp
     * @param object $object
     *
     * @throws Exception
     */
    public function delete(ConsumerObjectId $id, int $timestamp, object $object): void;

    /**
     * Get processed message for the webhook message.
     *
     * @param ConsumerMessageInterface $message
     * @param bool $isSkipped
     *
     * @return ProcessedMessageInterface
     *
     * @throws Exception
     */
    public function getProcessedMessage(
        ConsumerMessageInterface $message,
        bool $isSkipped
    ): ProcessedMessageInterface;

    /**
     * Upsert object into WP.
     *
     * @param ConsumerObjectId $id
     * @param int $timestamp
     * @param object $object
     *
     * @return EntityId Returns EntityId (entity ID and type).
     * @throws Exception
     */
    public function upsert(ConsumerObjectId $id, int $timestamp, object $object): EntityId;

    /**
     * Get WP entity bundle (taxonomy name or post type).
     *
     * Term "entity bundle" is chosen for this method because its author misses Drupal.
     *
     * @return string
     */
    public function wpEntityBundle(): string;
}
