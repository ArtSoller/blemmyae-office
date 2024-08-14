<?php

declare(strict_types=1);

use Cra\BlemmyaeApplications\BlemmyaeApplications;
use Cra\BlemmyaeApplications\Entity\Term;
use Cra\BlemmyaeDeployment\A9SMigration;
use Cra\BlemmyaeDeployment\SetupMappingsTable;
use ReleaseInstructions\Command\CoreCommand as RI;
use Scm\Tools\Logger;
use Scm\Tools\Utils;

function a9s143_migration_ri_001(): void
{
    (new RI())->setStatus('a9s143_migration_ri_003', true);
    (new RI())->setStatus('a9s143_migration_ri_004', true);
}

/**
 * Create new editorial types Alert and Ce2e for A9S posts.
 * @throws Exception
 */
function a9s143_migration_ri_002(): void
{
    $terms = [
        'Post'
    ];

    foreach ($terms as $term) {
        $newTerm = wp_create_term($term, 'editorial_type');

        if ($newTerm instanceof \WP_Error) {
            throw new Exception("Error while creating new term $term");
        }
    }

    wp_cache_flush();
}

/**
 * Migrate MSSP Alert Posts entity to Blemmyae Editorial entity.
 *
 * @throws Exception
 */
function a9s143_migration_ri_003(): void
{
    a9s143_migrate_posts(
        A9SMigration::ALERT_DB_NAME,
        A9SMigration::ALERT_POSTS_MAPPING_TABLE,
        A9SMigration::ALERT_ID_COLUMN,
        BlemmyaeApplications::MSSP,
        ['A9s', 'MSSP Alert'],
    );
}

/**
 * Migrate Channel E2E Posts entity to Blemmyae Editorial entity.
 *
 * @throws Exception
 */
function a9s143_migration_ri_004(): void
{
    a9s143_migrate_posts(
        A9SMigration::CE2E_DB_NAME,
        A9SMigration::CE2E_POSTS_MAPPING_TABLE,
        A9SMigration::CE2E_ID_COLUMN,
        BlemmyaeApplications::CE2E,
        ['A9s', 'ChannelE2E'],
    );
}

/**
 * Migrate Posts entity.
 *
 * @throws Exception
 */
function a9s143_migrate_posts(
    string $dbName,
    string $tableName,
    string $oldIdColumn,
    string $app,
    array $brands
): void {
    // Fix memory leak.
    wp_suspend_cache_addition(true);

    global $wpdb;

    $utils = new Utils();

    $utils->switchDB($dbName);
    $postIds = array_column($wpdb->get_results(
        "SELECT ID FROM `wp_posts` WHERE post_type='post' AND post_status!='auto-draft'"
    ), 'ID');

    // Get all post's data.
    $postsData = A9SMigration::getPostsData($postIds);
    $postsMeta = A9SMigration::getPostsMetaData($postIds);
    $postsCategories = A9SMigration::getPostsCategories($postIds);

    $utils->switchDBToDefault();

    $itemsCount = count($postIds);
    $count = 1;

    // Create mapping table.
    $setupMappingsTable = new SetupMappingsTable();
    $setupMappingsTable->setupTable(
        $tableName,
        $oldIdColumn,
        A9SMigration::BLEMMYAE_ID_COLUMN
    );

    $appTerm = Term::getAppTermBy('slug', $app);
    if (is_null($appTerm)) {
        Logger::log('Missing app term', 'error');
        return;
    }
    $editorialTypeTerm = get_term_by('name', 'Post', 'editorial_type');
    $brandTerms = [];
    foreach ($brands as $brand) {
        $brandTerms [] = get_term_by('name', $brand, 'brand');
    }

    foreach ($postIds as $id) {
        $msgPrefix = '[' . $count . '/' . $itemsCount . ']';
        $newId = A9SMigration::getMapping('post', $app, $id);
        $status = !empty($newId) ? 'updating' : 'creating';
        Logger::log("$msgPrefix: $id $status...", 'status');

        try {
            $options = [
                'newId' => $newId,
                'post' => $postsData[$id],
                'meta' => $postsMeta[$id],
                'categories' => $postsCategories[$id],
                'app' => $appTerm,
                'type' => $editorialTypeTerm,
                'brand' => $brandTerms,
            ];
            $editorial = A9SMigration::upsertEditorial($options);

            // Create id mapping for new entity.
            $result = A9SMigration::saveIdMapping(
                $tableName,
                $oldIdColumn,
                $id,
                $editorial->ID
            );

            if (!$result) {
                Logger::log("Could not insert mapping pair for WP User with ID: $id", 'notice');
            }

            Logger::log("$msgPrefix: $id ~> {$editorial->ID}", 'success');
            unset($editorial);
        } catch (Exception $exception) {
            Logger::log(
                $msgPrefix . 'Failed to create new Editorial for post with ID ' . $id,
                'warning'
            );
            Logger::log($exception->getMessage(), 'warning');
        }

        $count++;
    }

    wp_suspend_cache_addition(false);
}
