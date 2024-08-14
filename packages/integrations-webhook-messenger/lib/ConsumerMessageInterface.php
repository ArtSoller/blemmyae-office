<?php

/**
 * @licence proprietary
 *
 * @author Eugene Yakovenko <yakoveka@gmail.com>
 */

declare(strict_types=1);

namespace Cra\Integrations\WebhookMessenger;

use ValueError;

/**
 * Message interface for the consumer queue.
 */
interface ConsumerMessageInterface
{
    /**
     * Get object as a string.
     *
     * @return string
     */
    public function __toString(): string;

    /**
     * Return array of properties for asDataObject().
     *
     * @return array<string, mixed>
     */
    public function objectProperties(): array;

    /**
     * Get instance as plain data object.
     *
     * @return object
     */
    public function asDataObject(): object;

    /**
     * @return string
     */
    public function getEvent(): string;

    /**
     * @param string|null $event
     *
     * @throws ValueError
     */
    public function setEvent(?string $event): void;

    /**
     * @return ConsumerObjectId
     */
    public function getObjectId(): ConsumerObjectId;

    /**
     * @param ConsumerObjectId $objectId
     */
    public function setObjectId(ConsumerObjectId $objectId): void;

    /**
     * @return object
     */
    public function getObject(): object;

    /**
     * @param object|array|null $object
     *
     * @throws ValueError
     */
    public function setObject($object): void;

    /**
     * @return int
     */
    public function getTimestamp(): int;

    /**
     * @param int|null $timestamp
     *
     * @throws ValueError
     */
    public function setTimestamp(?int $timestamp): void;
}
