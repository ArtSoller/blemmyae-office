<?php

/**
 * PHPUnit bootstrap
 *
 * @package      Cra\BlemmyaeApplications\Tests
 * @author       Gary Jones
 * @copyright    2021 CRA
 * @license      proprietary
 */

declare(strict_types=1);

namespace Cra\BlemmyaeApplications\Tests;

// Check for a `--testsuite integration` arg when calling phpunit, and use it to conditionally load up WordPress.
$blemmyaeApplicationsArgv = $GLOBALS['argv'];
$blemmyaeApplicationsKey = (int)array_search('--testsuite', $blemmyaeApplicationsArgv, true);

if ($blemmyaeApplicationsKey && 'integration' === $blemmyaeApplicationsArgv[$blemmyaeApplicationsKey + 1]) {
    $blemmyaeApplicationsTestsDir = getenv('WP_TESTS_DIR');

    if (!$blemmyaeApplicationsTestsDir) {
        $blemmyaeApplicationsTestsDir = '/tmp/wordpress-tests-lib';
    }

    // Give access to tests_add_filter() function.
    require_once $blemmyaeApplicationsTestsDir . '/includes/functions.php';

    /**
     * Manually load the plugin being tested.
     */
    \tests_add_filter(
        'muplugins_loaded',
        static function () {
            require dirname(__DIR__) . '/blemmyae-applications.php';
        }
    );

    // Start up the WP testing environment.
    require $blemmyaeApplicationsTestsDir . '/includes/bootstrap.php';
}
