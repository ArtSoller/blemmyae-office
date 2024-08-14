<?php // phpcs:disable PSR1.Files.SideEffects.FoundWithSymbols

/**
 * Initialise the plugin
 *
 * This file can use syntax from the required level of PHP or later.
 *
 * @package   Cra\BlemmyaeDeployment
 * @author    Squiz Pty Ltd <products@squiz.net>
 * @copyright 2020 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   GPL-2.0-or-later
 */

declare(strict_types=1);

namespace Cra\BlemmyaeDeployment;

use BrightNucleus\Config\ConfigFactory;

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

if (!defined('BLEMMYAE_DEPLOYMENT_DIR')) {
    define('BLEMMYAE_DEPLOYMENT_DIR', plugin_dir_path(__FILE__));
}

if (!defined('BLEMMYAE_DEPLOYMENT_URL')) {
    define('BLEMMYAE_DEPLOYMENT_URL', plugin_dir_url(__FILE__));
}

/*
 * Initialize the plugin.
 */
$GLOBALS['blemmyae_deployment'] = new Plugin(
    ConfigFactory::create(__DIR__ . '/config/defaults.php')->getSubConfig('Cra\BlemmyaeDeployment')
);
$GLOBALS['blemmyae_deployment']->run();
