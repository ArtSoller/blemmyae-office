<?php

/**
 * @file Haymarket theme removal movement to headless.
 *
 * @link https://cra.myjetbrains.com/youtrack/issue/BLEM-171
 */

declare(strict_types=1);

/**
 * Changes theme to headless theme.
 */
function blem171_blemmyae_deployment_ri_001()
{
    $newTheme = 'headless';
    update_option('template', $newTheme);
    update_option('stylesheet', $newTheme);
    wp_cache_flush();

    if (defined('WP_CLI') && WP_CLI) {
        WP_CLI::success('New theme is ' . $newTheme);
    }
}
