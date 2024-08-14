<?php

/**
 * PHPUnit bootstrap
 *
 * @package      Cra\CtEditorial\Tests
 * @author       Gary Jones
 * @copyright    2021 CRA
 * @license      proprietary
 */

declare(strict_types=1);

namespace Cra\CtEditorial\Tests;

// Check for a `--testsuite integration` arg when calling phpunit, and use it to conditionally load up WordPress.
$ctEditorialArgv = $GLOBALS['argv'];
$ctEditorialKey = (int) array_search('--testsuite', $ctEditorialArgv, true);

if ($ctEditorialKey && 'integration' === $ctEditorialArgv[ $ctEditorialKey + 1 ]) {
    $ctEditorialTestsDir = getenv('WP_TESTS_DIR');

    if (! $ctEditorialTestsDir) {
        $ctEditorialTestsDir = '/tmp/wordpress-tests-lib';
    }

    // Give access to tests_add_filter() function.
    require_once $ctEditorialTestsDir . '/includes/functions.php';

    /**
     * Manually load the plugin being tested.
     */
    \tests_add_filter(
        'muplugins_loaded',
        static function () {
            require dirname(__DIR__) . '/ct-editorial.php';
        }
    );

    // Start up the WP testing environment.
    require $ctEditorialTestsDir . '/includes/bootstrap.php';
}
