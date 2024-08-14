<?php

/**
 * @link: https://cra.myjetbrains.com/youtrack/issue/INF-79
 */

declare(strict_types=1);

use Scm\Tools\Logger;

function inf79_blemmyae_deployment_ri_001(): void
{
    update_option('graphql_cache_section', [
        "global_max_age" => 600,
        "cache_toggle" => "on",
        "global_ttl" => 60,
        "purge_all" => false,
        "purge_all_timestamp" => false,
    ]);
    Logger::log('Updated `graphql_cache_section` option.', 'info');

    wp_cache_flush();
}
