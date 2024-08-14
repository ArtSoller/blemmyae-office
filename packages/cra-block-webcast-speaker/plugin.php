<?php
/**
 * Plugin Name: CRA Block: Webcast Speaker
 * Description: Gutenberg block for displaying a webcast speaker information
 * Author: Konstantin Gusev
 * Author URI: https://dotwrk.com/team
 * Version: 1.0.0
 * License: proprietary
 *
 * @package CRA
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Block Initializer.
 */
require_once plugin_dir_path(__FILE__) . 'src/init.php';
