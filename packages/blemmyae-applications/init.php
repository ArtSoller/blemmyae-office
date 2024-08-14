<?php // phpcs:disable PSR1.Files.SideEffects.FoundWithSymbols

/**
 * Initialise the plugin
 *
 * This file can use syntax from the required level of PHP or later.
 *
 * @package   Cra\BlemmyaeApplications
 * @author    Squiz Pty Ltd <products@squiz.net>
 * @copyright 2023 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   GPL-2.0-or-later
 */

declare(strict_types=1);

namespace Cra\BlemmyaeApplications;

use BrightNucleus\Config\ConfigFactory;

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

if (!defined('BLEMMYAE_APPLICATIONS_DIR')) {
    define('BLEMMYAE_APPLICATIONS_DIR', plugin_dir_path(__FILE__));
}

if (!defined('BLEMMYAE_APPLICATIONS_URL')) {
    define('BLEMMYAE_APPLICATIONS_URL', plugin_dir_url(__FILE__));
}

// Load Composer autoloader.
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    include_once __DIR__ . '/vendor/autoload.php';
}

/*
 * Initialize the plugin.
 */
$GLOBALS['blemmyae_applications'] = new Plugin(
    ConfigFactory::create(__DIR__ . '/config/defaults.php')->getSubConfig('Cra\BlemmyaeApplications')
);
$GLOBALS['blemmyae_applications']->run();
