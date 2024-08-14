<?php

/**
 * PHPUnit bootstrap
 *
 * @package      Cra\CtCompanyProfile\Tests
 * @author       Gary Jones
 * @copyright    2021 CRA
 * @license      proprietary
 */

declare(strict_types=1);

namespace Cra\CtCompanyProfile\Tests;

// Check for a `--testsuite integration` arg when calling phpunit, and use it to conditionally load up WordPress.
$ctCompanyProfileArgv = $GLOBALS['argv'];
$ctCompanyProfileKey = (int) array_search('--testsuite', $ctCompanyProfileArgv, true);

if ($ctCompanyProfileKey && 'integration' === $ctCompanyProfileArgv[ $ctCompanyProfileKey + 1 ]) {
    $ctCompanyProfileTestsDir = getenv('WP_TESTS_DIR');

    if (! $ctCompanyProfileTestsDir) {
        $ctCompanyProfileTestsDir = '/tmp/wordpress-tests-lib';
    }

    // Give access to tests_add_filter() function.
    require_once $ctCompanyProfileTestsDir . '/includes/functions.php';

    /**
     * Manually load the plugin being tested.
     */
    \tests_add_filter(
        'muplugins_loaded',
        static function () {
            require dirname(__DIR__) . '/ct-company-profile.php';
        }
    );

    // Start up the WP testing environment.
    require $ctCompanyProfileTestsDir . '/includes/bootstrap.php';
}
