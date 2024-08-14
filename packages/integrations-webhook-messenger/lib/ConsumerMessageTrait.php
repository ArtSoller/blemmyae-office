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
 * Trait for consumer messages logic. Contains getters, setters, constants.
 */
trait ConsumerMessageTrait
{
    protected string $event;

    protected ConsumerObjectId $objectId;

    protected object $object;

    protected int $timestamp;

    public const CREATE_EVENT = 'create';
    public const UPDATE_EVENT = 'update';
    public const DELETE_EVENT = 'delete';

    protected const SUPPORTED_EVENTS = [
        self::CREATE_EVENT,
        self::UPDATE_EVENT,
        self::DELETE_EVENT,
    ];

    /**
     * @inheritDoc
     */
    public function getEvent(): string
    {
        return $this->event ?? '';
    }

    /**
     * @inheritDoc
     */
    public function getObject(): object
    {
        return $this->object ?? (object)[];
    }

    /**
     * @inheritDoc
     */
    public function objectProperties(): array
    {
        return [
            'event' => $this->event,
            'objectId' => $this->objectId->asDataObject(),
            'object' => $this->object,
            'timestamp' => $this->timestamp,
        ];
    }

    /**
     * @inheritDoc
     */
    public function setEvent(?string $event): void
    {
        if (!in_array($event, self::SUPPORTED_EVENTS, true)) {
            throw new ValueError('"event" parameter must be one of the following: create, update, delete.');
        }
        $this->event = $event;
    }

    /**
     * @inheritDoc
     */
    public function getObjectId(): ConsumerObjectId
    {
        return $this->objectId;
    }

    /**
     * @inheritDoc
     */
    public function setObjectId(ConsumerObjectId $objectId): void
    {
        $this->objectId = $objectId;
    }

    /**
     * @inheritDoc
     */
    public function setObject($object): void
    {
        $this->validateValue($object, 'object');
        $this->object = (object)$object;
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return json_encode($this->asDataObject());
    }

    /**
     * @inheritDoc
     */
    public function asDataObject(): object
    {
        return (object)self::objectProperties();
    }

    /**
     * @inheritDoc
     */
    public function getTimestamp(): int
    {
        return $this->timestamp ?? 0;
    }

    /**
     * @inheritDoc
     */
    public function setTimestamp(?int $timestamp): void
    {
        $this->validateValue($timestamp, 'timestamp');
        $this->timestamp = $timestamp ?? 0;
    }
}
