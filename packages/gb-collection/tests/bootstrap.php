<?php

/**
 * PHPUnit bootstrap
 *
 * @package      Cra\GbCollection\Tests
 * @author       Gary Jones
 * @copyright    2021 CRA
 * @license      proprietary
 */

declare(strict_types=1);

namespace Cra\GbCollection\Tests;

// Check for a `--testsuite integration` arg when calling phpunit, and use it to conditionally load up WordPress.
$gbCollectionArgv = $GLOBALS['argv'];
$gbCollectionKey = (int) array_search('--testsuite', $gbCollectionArgv, true);

if ($gbCollectionKey && 'integration' === $gbCollectionArgv[ $gbCollectionKey + 1 ]) {
    $gbCollectionTestsDir = getenv('WP_TESTS_DIR');

    if (! $gbCollectionTestsDir) {
        $gbCollectionTestsDir = '/tmp/wordpress-tests-lib';
    }

    // Give access to tests_add_filter() function.
    require_once $gbCollectionTestsDir . '/includes/functions.php';

    /**
     * Manually load the plugin being tested.
     */
    \tests_add_filter(
        'muplugins_loaded',
        static function () {
            require dirname(__DIR__) . '/gb-collection.php';
        }
    );

    // Start up the WP testing environment.
    require $gbCollectionTestsDir . '/includes/bootstrap.php';
}
