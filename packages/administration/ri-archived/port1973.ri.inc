<?php

/**
 * PORT-1973.
 *
 * https://cra.myjetbrains.com/youtrack/issue/PORT-1973.
 */

declare(strict_types=1);

use ConvertToBlocks\MigrationCommand;
use ReleaseInstructions\Command\CoreCommand as RI;
use Scm\Tools\Logger;

const PORT1973_POSTS_LIST_TRANSIENT = "port1973_posts_list";

/**
 * Disable port1973_administration_ri_002, because it should be run manually.
 */
function port1973_administration_ri_005(): void
{
    Logger::log("Activate plugin...", "info");

    activate_plugin('convert-to-block');

    Logger::log("Enable convert to block for all post types.", "info");

    update_option('convert-to-blocks_post_types', [
        "advanced_ads",
        "company_profile",
        "editorial",
        "landing",
        "learning",
        "newsletter",
        "people",
        "product_profile",
        "whitepaper",
        "ppworks_announcement",
        "ppworks_article",
        "ppworks_episode",
        "ppworks_segment",
        "sc_award_nominee",
        "session",
        "ppworks_sponsor_prog",
    ]);

    Logger::log("Disable ri for migration and fixes. Please run all RIs manually", "info");

    // Disable update new migrated taxonomy RI.
    (new RI())->setStatus('port1973_administration_ri_012', true);
    (new RI())->setStatus('port1973_administration_ri_013', true);
}

/**
 * Prepare convert-to block migration.
 */
function port1973_administration_ri_012(): void
{
    global $wpdb;

    // Remove old transient.
    delete_transient('port1973_post_ids_migration_transient');

    // Init.
    $ids = [];

    // Migrated editorials.
    $posts_to_tables = [
        'wp_ce2e_posts_to_editorial',
        'wp_alert_posts_to_editorial'
    ];

    foreach ($posts_to_tables as $table) {
        $query = $wpdb->prepare("SELECT blem_id FROM %1s AS e
                            LEFT JOIN wp_posts p ON p.ID = e.blem_id
                            WHERE p.post_status = 'publish'
                            ORDER BY p.post_modified DESC;", $table);

        $ids = array_merge($ids, $wpdb->get_col($query));
    }

    // Migrated Learnings.
    $query = $wpdb->prepare("SELECT post_id FROM `wp_migration_mappings` as m
                            LEFT JOIN wp_posts p ON p.ID = m.post_id
                            WHERE p.post_status = 'publish' AND m.type = 'event'
                            ORDER BY p.post_modified DESC;");

    $ids = array_merge($ids, $wpdb->get_col($query));

    // Filter ids => return only posts with classic editor.
    /** @phpstan-ignore-next-line */
    $convertToBlockPlugin = new ConvertToBlocks\Plugin();

    $idsFiltered = array_filter($ids, function ($postId) use ($convertToBlockPlugin) {
        return $convertToBlockPlugin->is_classic_editor_post($postId);
    });

    // Log.
    $log = sprintf("Process of migration: %d / %d", count($ids) - count($idsFiltered), count($ids));
    Logger::log($log, 'info');

    // Update transient with new ids and expiration 30 days.
    set_transient(PORT1973_POSTS_LIST_TRANSIENT, $idsFiltered ?: [], 30 * 24 * 60 * 60);
}

/**
 * Run convert-to block migration.
 */
function port1973_administration_ri_013(): void
{
    // ELoad ids, which need to be migrated.
    $ids = get_transient(PORT1973_POSTS_LIST_TRANSIENT) ?: [];

    // Build options for migration command.
    $options = [
        'only' => implode(',', $ids),
        'post_type' => 'editorial',
        'learning',
    ];

    Logger::log(
        "After start you can interrup process with this RI, because migration will be done in browser",
        "info"
    );
    /** @phpstan-ignore-next-line */
    $migrationCommand = new MigrationCommand();

    Logger::log(
        "If you want to check migration status use `wp convert-to-blocks status --allow-root`",
        "info"
    );

    $migrationCommand->start([], $options);
}
