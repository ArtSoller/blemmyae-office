<?php

/**
 * PHPUnit bootstrap
 *
 * @package      Cra\Thumbor\Tests
 * @author       Gary Jones
 * @copyright    2021 CRA
 * @license      proprietary
 */

declare(strict_types=1);

namespace Cra\Thumbor\Tests;

// Check for a `--testsuite integration` arg when calling phpunit, and use it to conditionally load up WordPress.
$craThumborArgv = $GLOBALS['argv'];
$craThumborKey = (int) array_search('--testsuite', $craThumborArgv, true);

if ($craThumborKey && 'integration' === $craThumborArgv[$craThumborKey + 1]) {
    $craThumborTestsDir = getenv('WP_TESTS_DIR');

    if (!$craThumborTestsDir) {
        $craThumborTestsDir = '/tmp/wordpress-tests-lib';
    }

    // Give access to tests_add_filter() function.
    require_once $craThumborTestsDir . '/includes/functions.php';

    /**
     * Manually load the plugin being tested.
     */
    \tests_add_filter(
        'muplugins_loaded',
        static function () {
            require dirname(__DIR__) . '/cra-thumbor.php';
        }
    );

    // Start up the WP testing environment.
    require $craThumborTestsDir . '/includes/bootstrap.php';
}
