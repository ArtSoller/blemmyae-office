<?php

/**
 * PHPUnit bootstrap
 *
 * @package      Cra\BlemmyaePpworks\Tests
 * @author       Gary Jones
 * @copyright    2021 CRA
 * @license      proprietary
 */

declare(strict_types=1);

namespace Cra\BlemmyaePpworks\Tests;

// Check for a `--testsuite integration` arg when calling phpunit, and use it to conditionally load up WordPress.
$pluginSlugArgv = $GLOBALS['argv'];
$pluginSlugKey = (int)array_search('--testsuite', $pluginSlugArgv, true);

if ($pluginSlugKey && 'integration' === $pluginSlugArgv[$pluginSlugKey + 1]) {
    $pluginSlugTestsDir = getenv('WP_TESTS_DIR');

    if (!$pluginSlugTestsDir) {
        $pluginSlugTestsDir = '/tmp/wordpress-tests-lib';
    }

    // Give access to tests_add_filter() function.
    require_once $pluginSlugTestsDir . '/includes/functions.php';

    /**
     * Manually load the plugin being tested.
     */
    \tests_add_filter(
        'muplugins_loaded',
        static function () {
            require dirname(__DIR__) . '/blemmyae-ppworks.php';
        }
    );

    // Start up the WP testing environment.
    require $pluginSlugTestsDir . '/includes/bootstrap.php';
}
