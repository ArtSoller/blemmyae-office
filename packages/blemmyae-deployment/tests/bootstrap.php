<?php

/**
 * PHPUnit bootstrap
 *
 * @package      Cra\BlemmyaeDeployment\Tests
 * @author       Gary Jones
 * @copyright    2021 CRA
 * @license      proprietary
 */

declare(strict_types=1);

namespace Cra\BlemmyaeDeployment\Tests;

// Check for a `--testsuite integration` arg when calling phpunit, and use it to conditionally load up WordPress.
$blemmyaeDeploymentArgv = $GLOBALS['argv'];
$blemmyaeDeploymentKey = (int) array_search('--testsuite', $blemmyaeDeploymentArgv, true);

if (
    $blemmyaeDeploymentKey
    && 'integration' === $blemmyaeDeploymentArgv[ $blemmyaeDeploymentKey + 1 ]
) {
    $blemmyaeDeploymentTestsDir = getenv('WP_TESTS_DIR');

    if (! $blemmyaeDeploymentTestsDir) {
        $blemmyaeDeploymentTestsDir = '/tmp/wordpress-tests-lib';
    }

    // Give access to tests_add_filter() function.
    require_once $blemmyaeDeploymentTestsDir . '/includes/functions.php';

    /**
     * Manually load the plugin being tested.
     */
    \tests_add_filter(
        'muplugins_loaded',
        static function () {
            require dirname(__DIR__) . '/blemmyae-deployment.php';
        }
    );

    // Start up the WP testing environment.
    require $blemmyaeDeploymentTestsDir . '/includes/bootstrap.php';
}
