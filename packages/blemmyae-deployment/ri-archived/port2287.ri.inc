<?php

declare(strict_types=1);

use ReleaseInstructions\Command\CoreCommand as RI;
use Scm\Tools\Logger;

function port2287_blemmyae_deployment_ri_001(): void
{
    (new RI())->setStatus('port2287_blemmyae_deployment_ri_002', true);
    (new RI())->setStatus('port2287_blemmyae_deployment_ri_003', true);
}

/**
 * Create table for redirects custom storage.
 */
function port2287_blemmyae_deployment_ri_002(): void
{
    global $wpdb;

    $wpdb->query(
        "CREATE TABLE `wp_cra_redirects` (
            `source` VARCHAR(768) NOT NULL,
            `destination` VARCHAR(2000) NOT NULL,
            PRIMARY KEY `source` (`source`)
        ) {$wpdb->get_charset_collate()};"
    );
}

/**
 * Migrate redirects to custom redirects table.
 */
function port2287_blemmyae_deployment_ri_003(): void
{
    global $wpdb;

    // todo create a separate plugin for custom redirects logic.
    $tableName = 'wp_cra_redirects';
    $redirects = $wpdb->get_results("SELECT match_url, action_data FROM wp_redirection_items", ARRAY_A);
    $size = sizeof((array)$redirects);
    $count = 1;

    foreach ($redirects as $redirect) {
        $msgPrefix = '[' . $count . '/' . $size . ']';
        $dbEntry = $wpdb->get_results("SELECT * FROM $tableName WHERE source = '" . $redirect['match_url'] . "'");

        if (!$dbEntry) {
            $wpdb->insert($tableName, ['source' => $redirect['match_url'], 'destination' => $redirect['action_data']]);
        }

        Logger::log("$msgPrefix redirects processed", 'status');
        $count++;
    }
}
