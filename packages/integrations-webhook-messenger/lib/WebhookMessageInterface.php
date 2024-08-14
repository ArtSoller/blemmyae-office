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
interface WebhookMessageInterface extends ConsumerMessageInterface
{
    /**
     * @return string
     */
    public function getIssuer(): string;

    /**
     * @param string|null $issuer
     */
    public function setIssuer(?string $issuer): void;

    /**
     * @return int|string
     */
    public function getObjectVersion(): int|string;

    /**
     * @param int|null $objectVersion
     *
     * @throws ValueError
     */
    public function setObjectVersion(?int $objectVersion): void;

    /**
     * @return string
     */
    public function getReplyTo(): string;

    /**
     * @param string|null $replyTo
     */
    public function setReplyTo(?string $replyTo): void;
}
