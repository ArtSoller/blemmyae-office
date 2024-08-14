<?php

/**
 * PHPUnit bootstrap
 *
 * @package      Cra\CtWhitepaper\Tests
 * @author       Gary Jones
 * @copyright    2021 CRA
 * @license      proprietary
 */

declare(strict_types=1);

namespace Cra\CtWhitepaper\Tests;

// Check for a `--testsuite integration` arg when calling phpunit, and use it to conditionally load up WordPress.
$ctWhitepaperArgv = $GLOBALS['argv'];
$ctWhitepaperKey = (int) array_search('--testsuite', $ctWhitepaperArgv, true);

if ($ctWhitepaperKey && 'integration' === $ctWhitepaperArgv[ $ctWhitepaperKey + 1 ]) {
    $ctWhitepaperTestsDir = getenv('WP_TESTS_DIR');

    if (! $ctWhitepaperTestsDir) {
        $ctWhitepaperTestsDir = '/tmp/wordpress-tests-lib';
    }

    // Give access to tests_add_filter() function.
    require_once $ctWhitepaperTestsDir . '/includes/functions.php';

    /**
     * Manually load the plugin being tested.
     */
    \tests_add_filter(
        'muplugins_loaded',
        static function () {
            require dirname(__DIR__) . '/ct-whitepaper.php';
        }
    );

    // Start up the WP testing environment.
    require $ctWhitepaperTestsDir . '/includes/bootstrap.php';
}
