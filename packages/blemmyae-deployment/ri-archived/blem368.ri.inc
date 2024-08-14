<?php

/**
 * @link: https://cra.myjetbrains.com/youtrack/issue/BLEM-368
 */

declare(strict_types=1);

use DeliciousBrains\WP_Offload_SES\WP_Offload_SES;

/**
 * Import entity configs.
 */
function blem368_blemmyae_deployment_ri_001(): void
{
    $configOverrideSes = [
        'completed-setup' => '1',
        'default-email' => 'wordpress@scmagazine.com',
        'default-email-name' => 'SC Media',
        'enable-click-tracking' => '0',
        'enable-health-report' => '1',
        'enable-open-tracking' => '0',
        'health-report-frequency' => 'weekly',
        'health-report-recipients' => 'site-admins',
        'log-duration' => '30',
        'region' => 'us-east-2',
        'reply-to' => 'wordpress@scmagazine.com',
        'return-path' => 'wordpress@scmagazine.com',
        'send-via-ses' => '1',
    ];
    $optionKeySes = class_exists(
        'WP_Offload_SES'
    ) ? WP_Offload_SES::SETTINGS_KEY : 'wposes_settings';/** @phpstan-ignore-line */
    update_option($optionKeySes, array_replace(get_option($optionKeySes, []), $configOverrideSes));

    wp_cache_flush();
}
