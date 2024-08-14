<?php

/**
 * @link: https://cra.myjetbrains.com/youtrack/issue/PORT-1928/Simplify-legacy-redirects
 */

declare(strict_types=1);

use Scm\Tools\Logger;
use Scm\Tools\Utils;

/**
 * Clean out legacy redirects.
 */
function port1928_blemmyae_deployment_ri_001(): void
{
    global $wpdb;

    $redirectIds = array_map(
        static fn(array $row) => [
            'id' => $row[0],
        ],
        Utils::importCsv(__DIR__ . '/port1928/terms.csv')
    );

    foreach ($redirectIds as $item) {
        $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}redirection_items WHERE id=%d", $item['id']));
        Logger::log('Removed legacy redirect with id ' . $item['id'], 'info');
    }

    wp_cache_flush();
}
