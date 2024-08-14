<?php

/**
 * PHPUnit bootstrap
 *
 * @package      Cra\BlemmyaeAds\Tests
 * @author       Gary Jones
 * @copyright    2021 CRA
 * @license      proprietary
 */

declare(strict_types=1);

namespace Cra\BlemmyaeAds\Tests;

// Check for a `--testsuite integration` arg when calling phpunit, and use it to conditionally load up WordPress.
$blemmyaeAdsArgv = $GLOBALS['argv'];
$blemmyaeAdsKey = (int)array_search('--testsuite', $blemmyaeAdsArgv, true);

if ($blemmyaeAdsKey && 'integration' === $blemmyaeAdsArgv[$blemmyaeAdsKey + 1]) {
    $blemmyaeAdsTestsDir = getenv('WP_TESTS_DIR');

    if (!$blemmyaeAdsTestsDir) {
        $blemmyaeAdsTestsDir = '/tmp/wordpress-tests-lib';
    }

    // Give access to tests_add_filter() function.
    require_once $blemmyaeAdsTestsDir . '/includes/functions.php';

    /**
     * Manually load the plugin being tested.
     */
    \tests_add_filter(
        'muplugins_loaded',
        static function () {
            require dirname(__DIR__) . '/blemmyae-ads.php';
        }
    );

    // Start up the WP testing environment.
    require $blemmyaeAdsTestsDir . '/includes/bootstrap.php';
}
