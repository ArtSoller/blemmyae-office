<?php

/**
 * PHPUnit bootstrap
 *
 * @package      Cra\BlemmyaeBlocks\Tests
 * @author       Gary Jones
 * @copyright    2021 CRA
 * @license      proprietary
 */

declare(strict_types=1);

namespace Cra\BlemmyaeBlocks\Tests;

// Check for a `--testsuite integration` arg when calling phpunit, and use it to conditionally load up WordPress.
use function tests_add_filter;

$blemmyaeBlocksArgv = $GLOBALS['argv'];
$blemmyaeBlocksKey = (int)array_search('--testsuite', $blemmyaeBlocksArgv, true);

if ($blemmyaeBlocksKey && 'integration' === $blemmyaeBlocksArgv[$blemmyaeBlocksKey + 1]) {
    $blemmyaeBlocksTestsDir = getenv('WP_TESTS_DIR');

    if (!$blemmyaeBlocksTestsDir) {
        $blemmyaeBlocksTestsDir = '/tmp/wordpress-tests-lib';
    }

    // Give access to tests_add_filter() function.
    require_once $blemmyaeBlocksTestsDir . '/includes/functions.php';

    /**
     * Manually load the plugin being tested.
     */
    tests_add_filter(
        'muplugins_loaded',
        static function () {
            require dirname(__DIR__) . '/blemmyae-blocks.php';
        }
    );

    // Start up the WP testing environment.
    require $blemmyaeBlocksTestsDir . '/includes/bootstrap.php';
}
