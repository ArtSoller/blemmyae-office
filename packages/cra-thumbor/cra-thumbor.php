<?php // phpcs:disable PSR1.Files.SideEffects.FoundWithSymbols

/**
 * Plugin Name
 *
 * This file should only use syntax available in PHP 8.2 or later.
 *
 * @package   Cra\Thumbor
 * @author    Squiz Pty Ltd <products@squiz.net>
 * @copyright 2021 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   proprietary
 *
 * @wordpress-plugin
 * Plugin Name:       CRA Thumbor
 * Plugin URI:        https://github.com/cra-repo/cra-thumbor
 * Description:       Implements Thumbor service for intermediate image sizes.
 * Version:           1.1.0
 * Author:            CRA
 * Author URI:        https://www.cyberriskalliance.com
 * Text Domain:       cra-thumbor
 * License:           proprietary
 * GitHub Plugin URI: https://github.com/cra-repo/cra-thumbor
 * Requires PHP:      8.2
 * Requires WP:       5.6
 * RI:                true
 */

// If this file is called directly, abort.
if (defined('WPINC') === false) {
    die;
}

if (version_compare(PHP_VERSION, '7.4', '<') === true) {
    add_action('plugins_loaded', 'craThumborInitDeactivation');

    /**
     * Initialise deactivation functions.
     *
     * @return void
     */
    function craThumborInitDeactivation()
    {
        if (current_user_can('activate_plugins') === true) {
            add_action('admin_init', 'craThumborDeactivate');
            add_action('admin_notices', 'craThumborDeactivationNotice');
        }
    }

    /**
     * Deactivate the plugin.
     *
     * @return void
     */
    function craThumborDeactivate()
    {
        deactivate_plugins(plugin_basename(__FILE__));
    }

    /**
     * Show deactivation admin notice.
     *
     * @return void
     */
    function craThumborDeactivationNotice()
    {
        $notice = sprintf(
        // Translators: 1: Required PHP version, 2: Current PHP version.
            '<strong>CRA Thumbor</strong> requires PHP %1$s to run.
This site uses %2$s, so the plugin has been <strong>deactivated</strong>.',
            '8.2',
            PHP_VERSION
        );
        ?>
        <div class="updated"><p>
                <?php
                echo wp_kses_post($notice);
                ?>
            </p></div>
        <?php
        // phpcs:ignore WordPress.Security.NonceVerification, MySource.PHP.GetRequestData.SuperglobalAccessedWithVar
        if (isset($_GET['activate']) === true) {
            // phpcs:ignore WordPress.Security.NonceVerification, MySource.PHP.GetRequestData.SuperglobalAccessedWithVar
            unset($_GET['activate']);
        }
    }

    return false;
}//end if

/*
 * Load plugin initialisation file.
 */

require plugin_dir_path(__FILE__) . '/init.php';
