<?php

/**
 * @author  Konstantin Gusev <guvkon.net@icloud.com>
 * @license proprietary
 */

declare(strict_types=1);

namespace Cra\WebhookConsumer;

use Scm\Tools\PsrLogger;

/**
 * PSR-3 Logger class which adds webhook-consumer prefix, so it can be easily found in logs.
 */
final class Logger extends PsrLogger
{
    /**
     * @inheritDoc
     * @phpstan-ignore-next-line
     */
    public function log($level, $message, array $context = []): void
    {
        parent::log($level, 'WEBHOOK_CONSUMER ::: ' . $message, $context);
    }

    /**
     * @inheritDoc
     */
    public function error($message, array $context = []): void
    {
        parent::error('Error - ' . $message, $context);
    }

    /**
     * @inheritDoc
     */
    public function warning($message, array $context = []): void
    {
        parent::warning('Error - ' . $message, $context);
    }

    /**
     * @inheritDoc
     */
    public function debug($message, array $context = []): void
    {
        if (Webhook::isDebug()) {
            parent::info($message, $context);
        }
    }
}
