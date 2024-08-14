<?php

/**
 * @link: https://cra.myjetbrains.com/youtrack/issue/BLEM-618
 */

declare(strict_types=1);

use ReleaseInstructions\Command\CoreCommand as RI;

function blem618_blemmyae_deployment_ri_001(): void
{
    (new RI())->setStatus('blem618_blemmyae_deployment_ri_002', true);
}

/**
 * Sets post status of all active convertr whitepapers to draft,
 * removes deprecated cron schedules
 *
 * @return void
 */
function blem618_blemmyae_deployment_ri_002(): void
{
    $deprecatedCronSchedules = [
        'convertr_whitepaper_sync_hourly',
        'convertr_whitepaper_sync_manual',
    ];

    foreach ($deprecatedCronSchedules as $cronSchedule) {
        wp_clear_scheduled_hook($cronSchedule);
    }

    $wpQuery = new WP_Query([
        'post_type' => 'whitepaper',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'meta_query' => [
            [
                'key' => 'vendor',
                'value' => ':"convertr";',
                'compare' => 'LIKE',
            ],
        ],
    ]);

    $convertrWhitepapers = $wpQuery->get_posts();

    foreach ($convertrWhitepapers as $convertrWhitepaperId) {
        wp_update_post(
            [
                'ID' => $convertrWhitepaperId,
                'post_status' => 'draft',
            ]
        );
    }
    wp_cache_flush();
}
