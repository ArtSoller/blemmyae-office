<?php

/**
 * PHPUnit bootstrap
 *
 * @package      Cra\CtLearning\Tests
 * @author       Gary Jones
 * @copyright    2021 CRA
 * @license      proprietary
 */

declare(strict_types=1);

namespace Cra\CtLearning\Tests;

// Check for a `--testsuite integration` arg when calling phpunit, and use it to conditionally load up WordPress.
$ctLearningArgv = $GLOBALS['argv'];
$ctLearningKey = (int) array_search('--testsuite', $ctLearningArgv, true);

if ($ctLearningKey && 'integration' === $ctLearningArgv[ $ctLearningKey + 1 ]) {
    $ctLearningTestsDir = getenv('WP_TESTS_DIR');

    if (! $ctLearningTestsDir) {
        $ctLearningTestsDir = '/tmp/wordpress-tests-lib';
    }

    // Give access to tests_add_filter() function.
    require_once $ctLearningTestsDir . '/includes/functions.php';

    /**
     * Manually load the plugin being tested.
     */
    \tests_add_filter(
        'muplugins_loaded',
        static function () {
            require dirname(__DIR__) . '/ct-learning.php';
        }
    );

    // Start up the WP testing environment.
    require $ctLearningTestsDir . '/includes/bootstrap.php';
}
