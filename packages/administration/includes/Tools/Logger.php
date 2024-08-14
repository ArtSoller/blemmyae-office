<?php

/**
 * Logger – Custom Logger.
 *
 * @author Alexander Kucherov (avdkucherov@gmail.com)
 */

namespace Scm\Tools;

class Logger
{
    /**
     * Defines the logger method to use and outputs messages.
     *
     * @param string $message Message text.
     * @param string $type Message type.
     *
     * @since 1.0.0
     */
    public static function log(string $message, string $type)
    {
        if (Utils::isCLI()) {
            self::cli($message, $type);
            return;
        }

        $formattedMessage = ($type ? '[' . $type . ']: ' : '') . $message;
        \error_log($formattedMessage);
    }

    /**
     * Using WP_CLI methods for output.
     *
     * @param string $message Message text.
     * @param string $type Message type.
     *
     * @since  1.0.0
     * @access protected
     */
    protected static function cli(string $message, string $type)
    {
        /*
         * WP_CLI output methods.
         *
         * @see https://make.wordpress.org/cli/handbook/references/internal-api/#output
         */
        switch ($type) {
            case 'success':
                \WP_CLI::success($message);
                break;

            case 'status':
            case 'found':
            case 'info':
                \WP_CLI::log(($type ? '[' . $type . ']: ' : '') . $message);
                break;

            case 'notice':
            case 'warning':
                \WP_CLI::warning($message);
                break;

            case 'error':
                \WP_CLI::error($message);
                break;

            default:
                \WP_CLI::log($message);
        }
    }
}
