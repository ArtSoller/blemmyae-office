<?php // phpcs:disable PSR1.Files.SideEffects.FoundWithSymbols

/**
 * Plugin Name
 *
 * This file should only use syntax available in PHP 7.4 or later.
 *
 * @package   Cra\BlemmyaeBlocks
 * @author    Squiz Pty Ltd <products@squiz.net>
 * @copyright 2021 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   proprietary
 *
 * @wordpress-plugin
 * Plugin Name:       Blemmyae Blocks
 * Plugin URI:        https://github.com/cra-repo/blemmyae-blocks
 * Description:       ...
 * Version:           3.90.0
 * Author:            CRA
 * Author URI:        https://www.cyberriskalliance.com
 * Text Domain:       blemmyae-blocks
 * License:           proprietary
 * GitHub Plugin URI: https://github.com/cra-repo/blemmyae-blocks
 * Requires PHP:      7.4
 * Requires WP:       5.6
 * RI: true
 */

// If this file is called directly, abort.
if (defined('WPINC') === false) {
    die;
}

if (version_compare(PHP_VERSION, '7.4', '<') === true) {
    add_action('plugins_loaded', 'blemmyaeBlocksInitDeactivation');

    /**
     * Initialise deactivation functions.
     *
     * @return void
     */
    function blemmyaeBlocksInitDeactivation(): void
    {
        if (current_user_can('activate_plugins') === true) {
            add_action('admin_init', 'blemmyaeBlocksDeactivate');
            add_action('admin_notices', 'blemmyaeBlocksDeactivationNotice');
        }
    }

    /**
     * Deactivate the plugin.
     *
     * @return void
     */
    function blemmyaeBlocksDeactivate(): void
    {
        deactivate_plugins(plugin_basename(__FILE__));
    }

    /**
     * Show deactivation admin notice.
     *
     * @return void
     */
    function blemmyaeBlocksDeactivationNotice(): void
    {
        $notice = sprintf(
        // Translators: 1: Required PHP version, 2: Current PHP version.
            '<strong>Blemmyae Blocks</strong> requires PHP %1$s to run.
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

/*
 * Load plugin initialisation file.
 */

require plugin_dir_path(__FILE__) . '/init.php';
