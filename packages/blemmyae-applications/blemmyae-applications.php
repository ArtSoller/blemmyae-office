<?php // phpcs:disable PSR1.Files.SideEffects.FoundWithSymbols

/**
 * Plugin Name
 *
 * This file should only use syntax available in PHP 7.4 or later.
 *
 * @package   Cra\BlemmyaeApplications
 * @author    Squiz Pty Ltd <products@squiz.net>
 * @copyright 2021 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   proprietary
 *
 * @wordpress-plugin
 * Plugin Name:       Blemmyae Applications
 * Plugin URI:        https://github.com/cra-repo/...
 * Description:       ...
 * Version:           0.1.0
 * Author:            CRA
 * Author URI:        https://www.cyberriskalliance.com
 * Text Domain:       blemmyae-applications
 * License:           proprietary
 * GitHub Plugin URI: https://github.com/cra-repo/...
 * Requires PHP:      7.4
 * Requires WP:       5.6
 */

use Cra\BlemmyaeApplications\BlemmyaeApplications as BlemmyaeApplications;
use Cra\BlemmyaeApplications\CerberusApps;

// If this file is called directly, abort.
if (defined('WPINC') === false) {
    die;
}

if (version_compare(PHP_VERSION, '7.4', '<') === true) {
    add_action('plugins_loaded', 'blemmyaeApplicationsInitDeactivation');

    /**
     * Initialise deactivation functions.
     *
     * @return void
     */
    function blemmyaeApplicationsInitDeactivation(): void
    {
        if (current_user_can('activate_plugins') === true) {
            add_action('admin_init', 'blemmyaeApplicationsDeactivate');
            add_action('admin_notices', 'blemmyaeApplicationsDeactivationNotice');
        }
    }

    /**
     * Deactivate the plugin.
     *
     * @return void
     */
    function blemmyaeApplicationsDeactivate(): void
    {
        deactivate_plugins(plugin_basename(__FILE__));
    }

    /**
     * Show deactivation admin notice.
     *
     * @return void
     */
    function blemmyaeApplicationsDeactivationNotice(): void
    {
        $notice = sprintf(
        // Translators: 1: Required PHP version, 2: Current PHP version.
            '<strong>Blemmyae Applications</strong> requires PHP %1$s to run.
This site uses %2$s, so the plugin has been <strong>deactivated</strong>.',
            '7.4',
            PHP_VERSION
        );
        ?>
        <div class="updated"><p>
                <?php
                echo wp_kses_post($notice);
                ?>
            </p></div>
        <?php
        if (isset($_GET['activate']) === true) {
            unset($_GET['activate']);
        }
    }

    return false;
}//end if

new BlemmyaeApplications();
new CerberusApps();

/*
 * Load plugin initialisation file.
 */

require plugin_dir_path(__FILE__) . '/init.php';
