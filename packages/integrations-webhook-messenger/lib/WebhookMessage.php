<?php

/**
 * @licence proprietary
 *
 * @author  Konstantin Gusev <guvkon.net@icloud.com>
 */

declare(strict_types=1);

namespace Cra\Integrations\WebhookMessenger;

/**
 * Message class for the webhook queue.
 */
class WebhookMessage extends ConsumerMessage implements WebhookMessageInterface
{
    protected object $object;

    protected int|string $objectVersion;

    protected string $replyTo;

    protected string $issuer;

    /**
     * @inheritDoc
     */
    public function __construct(array $message)
    {
        parent::__construct($message);

        $this->setIssuer($message['issuer'] ?? null);
        $this->setObjectVersion($message['objectVersion'] ?? null);
        $this->setReplyTo($message['replyTo'] ?? null);
    }

    /**
     * @inheritDoc
     */
    public function asDataObject(): object
    {
        return (object)array_merge(parent::objectProperties(), ['objectVersion' => $this->objectVersion]);
    }

    /**
     * @inheritDoc
     */
    public function getIssuer(): string
    {
        return $this->issuer;
    }

    /**
     * @inheritDoc
     */
    public function setIssuer(?string $issuer): void
    {
        $this->validateValue($issuer, 'issuer');
        $this->issuer = $issuer ?: '';
    }

    /**
     * @inheritDoc
     */
    public function getObjectVersion(): int|string
    {
        return $this->objectVersion;
    }

    /**
     * @inheritDoc
     */
    public function setObjectVersion(int|string|null $objectVersion): void
    {
        $this->validateValue($objectVersion, 'objectVersion');
        $this->objectVersion = $objectVersion ?: 0;
    }

    /**
     * @inheritDoc
     */
    public function getReplyTo(): string
    {
        return $this->replyTo;
    }

    /**
     * @inheritDoc
     */
    public function setReplyTo(?string $replyTo): void
    {
        $this->replyTo = $replyTo ?: 'none';
    }
}
