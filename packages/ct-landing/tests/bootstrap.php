<?php

/**
 * PHPUnit bootstrap
 *
 * @package      Cra\CtLanding\Tests
 * @author       Gary Jones
 * @copyright    2021 CRA
 * @license      proprietary
 */

declare(strict_types=1);

namespace Cra\CtLanding\Tests;

// Check for a `--testsuite integration` arg when calling phpunit, and use it to conditionally load up WordPress.
$ctLandingArgv = $GLOBALS['argv'];
$ctLandingKey = (int) array_search('--testsuite', $ctLandingArgv, true);

if ($ctLandingKey && 'integration' === $ctLandingArgv[ $ctLandingKey + 1 ]) {
    $ctLandingTestsDir = getenv('WP_TESTS_DIR');

    if (! $ctLandingTestsDir) {
        $ctLandingTestsDir = '/tmp/wordpress-tests-lib';
    }

    // Give access to tests_add_filter() function.
    require_once $ctLandingTestsDir . '/includes/functions.php';

    /**
     * Manually load the plugin being tested.
     */
    \tests_add_filter(
        'muplugins_loaded',
        static function () {
            require dirname(__DIR__) . '/ct-landing.php';
        }
    );

    // Start up the WP testing environment.
    require $ctLandingTestsDir . '/includes/bootstrap.php';
}
