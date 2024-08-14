<?php

/**
 * PHPUnit bootstrap
 *
 * @package      Cra\WebhookConsumer\Tests
 * @author       Gary Jones
 * @copyright    2021 CRA
 * @license      proprietary
 */

declare(strict_types=1);

namespace Cra\WebhookConsumer\Tests;

// Check for a `--testsuite integration` arg when calling phpunit, and use it to conditionally load up WordPress.
$webhookConsumerArgv = $GLOBALS['argv'];
$webhookConsumerKey = (int)array_search('--testsuite', $webhookConsumerArgv, true);

if ($webhookConsumerKey && 'integration' === $webhookConsumerArgv[$webhookConsumerKey + 1]) {
    $webhookConsumerTestsDir = getenv('WP_TESTS_DIR');

    if (!$webhookConsumerTestsDir) {
        $webhookConsumerTestsDir = '/tmp/wordpress-tests-lib';
    }

    // Give access to tests_add_filter() function.
    require_once $webhookConsumerTestsDir . '/includes/functions.php';

    /**
     * Manually load the plugin being tested.
     */
    \tests_add_filter(
        'muplugins_loaded',
        static function () {
            require dirname(__DIR__) . '/webhook-consumer.php';
        }
    );

    // Start up the WP testing environment.
    require $webhookConsumerTestsDir . '/includes/bootstrap.php';
}
