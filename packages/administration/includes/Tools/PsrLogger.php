<?php

/**
 * @licence proprietary
 *
 * @author Konstantin Gusev <guvkon.net@icloud.com>
 */

declare(strict_types=1);

namespace Scm\Tools;

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

/**
 * PSR compliant logger class.
 */
class PsrLogger extends AbstractLogger
{
    /**
     * Log message.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     */
    public function log($level, $message, array $context = [])
    {
        if ($this->shouldSkipLogging($level)) {
            return;
        }

        $replacePairs = [];
        foreach ($context as $key => $value) {
            $replacePairs['{' . $key . '}'] = var_export($value, true);
        }
        $filteredMessage = strtr($message, $replacePairs);

        // Avoid unexpected output for graphql requests.
        if (function_exists('is_graphql_request') && is_graphql_request()) {
            graphql_debug($filteredMessage, ['type' => $level]);
            return;
        }

        Logger::log(
            sprintf(
                "%s | %s | %s\n%s",
                $level,
                gmdate('c'),
                $filteredMessage,
                json_encode($context)
            ),
            $level
        );
    }

    /**
     * Checks if logging for the $level should be skipped.
     *
     * @param mixed $level
     *
     * @return bool
     */
    private function shouldSkipLogging(mixed $level): bool
    {
        return Utils::isProd() && $level === LogLevel::DEBUG;
    }
}
