<?php

/**
 * @link https://cra.myjetbrains.com/youtrack/issue/PORT-2303
 *
 * @author  Olga Kiriliuk <fialka.chan@gmail.com>
 * @license proprietary
 */

declare(strict_types=1);

use Scm\Tools\Logger;

/**
 * .
 *
 * @return void
 */
function port2303_cra_thumbor_ri_001(): void
{
    global $wpdb;

    $wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . 'ewwwio_images');
    $wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . 'ewwwio_queue');
    Logger::log("Deleted EWWW Image Optimizer tables.", "info");

    $query = "SELECT option_name FROM $wpdb->options WHERE option_name LIKE '%ewww%'";
    $options = $wpdb->get_results( $query );

    foreach ($options as $option) {
        delete_option($option->option_name);
        Logger::log("Deleted EWWW Image Optimizer option: $option->option_name.", "info");
    }
}

