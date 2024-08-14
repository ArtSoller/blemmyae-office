<?php

/**
 * PHPUnit bootstrap
 *
 * @package      Cra\CtProductProfile\Tests
 * @author       Gary Jones
 * @copyright    2021 CRA
 * @license      proprietary
 */

declare(strict_types=1);

namespace Cra\CtProductProfile\Tests;

// Check for a `--testsuite integration` arg when calling phpunit, and use it to conditionally load up WordPress.
$ctProductProfileArgv = $GLOBALS['argv'];
$ctProductProfileKey = (int) array_search('--testsuite', $ctProductProfileArgv, true);

if ($ctProductProfileKey && 'integration' === $ctProductProfileArgv[ $ctProductProfileKey + 1 ]) {
    $ctProductProfileTestsDir = getenv('WP_TESTS_DIR');

    if (! $ctProductProfileTestsDir) {
        $ctProductProfileTestsDir = '/tmp/wordpress-tests-lib';
    }

    // Give access to tests_add_filter() function.
    require_once $ctProductProfileTestsDir . '/includes/functions.php';

    /**
     * Manually load the plugin being tested.
     */
    \tests_add_filter(
        'muplugins_loaded',
        static function () {
            require dirname(__DIR__) . '/ct-product-profile.php';
        }
    );

    // Start up the WP testing environment.
    require $ctProductProfileTestsDir . '/includes/bootstrap.php';
}
