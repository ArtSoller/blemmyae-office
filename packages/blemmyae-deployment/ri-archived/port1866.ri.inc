<?php

declare(strict_types=1);

use Cra\BlemmyaeApplications\BlemmyaeApplications;
use Cra\BlemmyaeDeployment\A9SMigration;
use Cra\CtEditorial\EditorialCT;
use ReleaseInstructions\Command\CoreCommand as RI;
use Scm\Tools\Logger;

const PORT1866_POST_TYPE_EVENT = 'event';
function port1866_migration_ri_001(): void
{
    (new RI())->setStatus('port1866_migration_ri_002', true);
    (new RI())->setStatus('port1866_migration_ri_003', true);
}

/**
 * Update links inside content of Blemmyae Editorial entity for app Mssp.
 *
 * @throws Exception
 */
function port1866_migration_ri_002(): void
{
    // Update Editorial content.
    port1866_update_posts(
        A9SMigration::ALERT_POSTS_MAPPING_TABLE,
        BlemmyaeApplications::MSSP,
        EditorialCT::POST_TYPE
    );
}

/**
 * Update links inside content of Blemmyae Editorial entity for app Ce2e.
 *
 * @throws Exception
 */
function port1866_migration_ri_003(): void
{
    // Update Editorial content.
    port1866_update_posts(
        A9SMigration::CE2E_POSTS_MAPPING_TABLE,
        BlemmyaeApplications::CE2E,
        EditorialCT::POST_TYPE
    );
}

/**
 * @param string $tableName
 * @param string $oldIdColumn
 * @param string $app
 * @param array $brands
 * @return void
 */
function port1866_update_posts(
    string $tableName,
    string $app,
    string $type
): void {
    wp_suspend_cache_addition(true);
    global $wpdb;
    $postIds = [];
    $blemIdCol = 'blem_id';
    $postIdCol = 'post_id';

    switch ($tableName) {
        case A9SMigration::ALERT_POSTS_MAPPING_TABLE:
        case A9SMigration::CE2E_POSTS_MAPPING_TABLE:
            $postIds = array_column($wpdb->get_results(
                "SELECT $blemIdCol FROM $tableName"
            ), $blemIdCol);
            break;
        case 'wp_migration_mappings':
            $postIds = array_column($wpdb->get_results(
                "SELECT $postIdCol FROM $tableName WHERE vendor='$app' AND type='$type'"
            ), $postIdCol);
            break;
        default:
            Logger::log("There are no suitable cases for Editorial entity", 'error');
    }

    if (empty($postIds)) {
        Logger::log("Unable to get posts' ids for entity type $type", 'error');
    }

    $itemsCount = count($postIds);
    $count = 1;

    foreach ($postIds as $id) {
        $msgPrefix = '[' . $count . '/' . $itemsCount . ']';
        Logger::log("$msgPrefix: $id updating...", 'status');
        $count++;

        $postsData = A9SMigration::getPostsData([$id]);

        $content = A9SMigration::replaceUrl($postsData[$id]['post_content'], $app);

        if ($content === $postsData[$id]['post_content']) {
            continue;
        } else {
            // Update Post Content.
            wp_update_post([
                'ID' => $id,
                'post_content' => $content
            ]);

            wp_cache_flush();
        }
    }

    wp_suspend_cache_addition(false);
}
