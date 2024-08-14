<?php

/**
 * @author  Konstantin Gusev <guvkon.net@icloud.com>
 * @author  Alexander Kucherov <avdkucherov@gmail.com>
 *
 * @license proprietary
 */

declare(strict_types=1);

namespace Cra\BlemmyaeDeployment\A9SMigration;

use Scm\Tools\PsrLogger;
use Scm\Tools\Utils;

/**
 * PSR-3 Logger class which adds webhook-consumer prefix, so it can be easily found in logs.
 */
final class Logger extends PsrLogger
{
    /**
     * @inheritDoc
     */
    public function log(
        $level,
        $message,
        array $context = []
    ): void {
        parent::log($level, 'MIGRATION_PROCESSING ::: ' . $message, $context);
    }

    /**
     * @inheritDoc
     */
    public function debug(
        $message,
        array $context = []
    ): void {
        if (Utils::isCLI()) {
            parent::info($message, $context);
        }
    }
}
