<?php

/**
 * PHPUnit bootstrap
 *
 * @package      Cra\CtPeople\Tests
 * @author       Gary Jones
 * @copyright    2021 CRA
 * @license      proprietary
 */

declare(strict_types=1);

namespace Cra\CtPeople\Tests;

// Check for a `--testsuite integration` arg when calling phpunit, and use it to conditionally load up WordPress.
$ctPeopleArgv = $GLOBALS['argv'];
$ctPeopleKey = (int) array_search('--testsuite', $ctPeopleArgv, true);

if ($ctPeopleKey && 'integration' === $ctPeopleArgv[ $ctPeopleKey + 1 ]) {
    $ctPeopleTestsDir = getenv('WP_TESTS_DIR');

    if (! $ctPeopleTestsDir) {
        $ctPeopleTestsDir = '/tmp/wordpress-tests-lib';
    }

    // Give access to tests_add_filter() function.
    require_once $ctPeopleTestsDir . '/includes/functions.php';

    /**
     * Manually load the plugin being tested.
     */
    \tests_add_filter(
        'muplugins_loaded',
        static function () {
            require dirname(__DIR__) . '/ct-people.php';
        }
    );

    // Start up the WP testing environment.
    require $ctPeopleTestsDir . '/includes/bootstrap.php';
}
