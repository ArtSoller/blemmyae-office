<?php

/**
 * PHPUnit bootstrap
 *
 * @package      Cra\CtNewsletter\Tests
 * @author       Gary Jones
 * @copyright    2021 CRA
 * @license      proprietary
 */

declare(strict_types=1);

namespace Cra\CtNewsletter\Tests;

// Check for a `--testsuite integration` arg when calling phpunit, and use it to conditionally load up WordPress.
$ctNewsletterArgv = $GLOBALS['argv'];
$ctNewsletterKey = (int) array_search('--testsuite', $ctNewsletterArgv, true);

if ($ctNewsletterKey && 'integration' === $ctNewsletterArgv[ $ctNewsletterKey + 1 ]) {
    $ctNewsletterTestsDir = getenv('WP_TESTS_DIR');

    if (! $ctNewsletterTestsDir) {
        $ctNewsletterTestsDir = '/tmp/wordpress-tests-lib';
    }

    // Give access to tests_add_filter() function.
    require_once $ctNewsletterTestsDir . '/includes/functions.php';

    /**
     * Manually load the plugin being tested.
     */
    \tests_add_filter(
        'muplugins_loaded',
        static function () {
            require dirname(__DIR__) . '/ct-newsletter.php';
        }
    );

    // Start up the WP testing environment.
    require $ctNewsletterTestsDir . '/includes/bootstrap.php';
}
