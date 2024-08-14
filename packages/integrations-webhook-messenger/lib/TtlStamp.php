<?php

/**
 * @author  Konstantin Gusev <guvkon.net@icloud.com>
 * @license proprietary
 */

declare(strict_types=1);

namespace Cra\Integrations\WebhookMessenger;

use Symfony\Component\Messenger\Stamp\StampInterface;

/**
 * Stamp interface which indicates Time To Live (TTL) for the associated message.
 *
 * If a message's TTL gets to zero and the message hasn't been processed successfully
 * then it should put into the failed queue instead of the processing queue for retries.
 */
final class TtlStamp implements StampInterface
{
    private int $ttl;

    public function __construct(int $ttl = 7)
    {
        $this->ttl = $ttl;
    }

    /**
     * Determines if the associated messages should be retried or failed.
     *
     * @return bool
     */
    public function isProcessable(): bool
    {
        return $this->ttl > 0;
    }

    /**
     * Decrease TTL.
     *
     * @return $this
     */
    public function decrease(): self
    {
        $this->ttl -= 1;

        return $this;
    }
}
