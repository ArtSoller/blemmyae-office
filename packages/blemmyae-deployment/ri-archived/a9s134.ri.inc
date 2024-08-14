<?php

declare(strict_types=1);

use Cra\BlemmyaeApplications\BlemmyaeApplications;
use Cra\BlemmyaeDeployment\A9SMigration;
use Cra\BlemmyaeDeployment\SetupMappingsTable;
use ReleaseInstructions\Command\CoreCommand as RI;
use Scm\Tools\Logger;
use Scm\Tools\Utils;

function a9s134_migration_ri_001(): void
{
    (new RI())->setStatus('a9s134_migration_ri_002', true);
    (new RI())->setStatus('a9s134_migration_ri_003', true);
}

/**
 * Migrate MSSP Alert Authors to Blemmyae People entity.
 *
 * @return void
 * @throws Exception
 */
function a9s134_migration_ri_002(): void
{
    // Fix memory leak.
    wp_suspend_cache_addition(true);

    global $wpdb;

    $utils = new Utils();

    $utils->switchDB(A9SMigration::ALERT_DB_NAME);
    $authorsIds = array_column($wpdb->get_results(
        "SELECT DISTINCT post_author FROM `wp_posts` WHERE post_type='post'"
    ), 'post_author');
    $users = A9SMigration::getAllUsersData($authorsIds);

    $utils->switchDBToDefault();

    $itemsCount = sizeof($users);
    $count = 1;

    // Create mapping table.
    $setupMappingsTable = new SetupMappingsTable();
    $setupMappingsTable->setupTable(
        A9SMigration::ALERT_AUTHOR_MAPPING_TABLE,
        A9SMigration::ALERT_ID_COLUMN,
        A9SMigration::BLEMMYAE_ID_COLUMN
    );

    foreach ($users as $user) {
        $msgPrefix = '[' . $count . '/' . $itemsCount . ']';

        $id = $user['id'];
        $newId = A9SMigration::getMapping('author', BlemmyaeApplications::MSSP, $id);
        $status = !empty($newId) ? 'updating' : 'creating';
        Logger::log("$msgPrefix: $id $status...", 'status');

        // Upsert person.
        $pageId = Cra\BlemmyaeDeployment\A9SMigration::upsertPerson($user, $newId);

        if (empty($pageId)) {
            Logger::log($msgPrefix . 'Failed to create/update person: ', 'notice');
        }

        // Save alert_id -> blem_id mapping.
        $result = A9SMigration::saveIdMapping(
            A9SMigration::ALERT_AUTHOR_MAPPING_TABLE,
            A9SMigration::ALERT_ID_COLUMN,
            $user['id'],
            $pageId
        );

        if (!$result) {
            Logger::log(
                "Could not insert mapping pair for WP User with ID: {$user['id']}",
                'notice'
            );
        }

        Logger::log(
            'Processed ' . $msgPrefix . ' people, current person id: ' . $user['id'],
            'status'
        );
        $count++;
    }

    wp_suspend_cache_addition(false);
}

/**
 * Migrate CE2E Authors to Blemmyae People entity.
 *
 * @return void
 * @throws Exception
 */
function a9s134_migration_ri_003(): void
{
    // Fix memory leak.
    wp_suspend_cache_addition(true);

    global $wpdb;

    $utils = new Utils();

    $utils->switchDB(A9SMigration::CE2E_DB_NAME);
    $authorsIds = array_column($wpdb->get_results(
        "SELECT DISTINCT post_author FROM `wp_posts` WHERE post_type='post'"
    ), 'post_author');
    $users = A9SMigration::getAllUsersData($authorsIds);

    $utils->switchDBToDefault();

    $itemsCount = sizeof($users);
    $count = 1;

    // Create mapping table.
    $setupMappingsTable = new SetupMappingsTable();
    $setupMappingsTable->setupTable(
        A9SMigration::CE2E_AUTHOR_MAPPING_TABLE,
        A9SMigration::CE2E_ID_COLUMN,
        A9SMigration::BLEMMYAE_ID_COLUMN
    );

    foreach ($users as $user) {
        $msgPrefix = '[' . $count . '/' . $itemsCount . ']';

        $id = $user['id'];
        $newId = A9SMigration::getMapping('author', BlemmyaeApplications::CE2E, $id);
        $status = !empty($newId) ? 'updating' : 'creating';
        Logger::log("$msgPrefix: $id $status...", 'status');

        // Upsert person.
        $pageId = Cra\BlemmyaeDeployment\A9SMigration::upsertPerson($user, $newId);

        if (empty($pageId)) {
            Logger::log($msgPrefix . 'Failed to create/update person: ', 'notice');
        }

        // Save alert_id -> blem_id mapping.
        $result = A9SMigration::saveIdMapping(
            A9SMigration::CE2E_AUTHOR_MAPPING_TABLE,
            A9SMigration::CE2E_ID_COLUMN,
            $user['id'],
            $pageId
        );

        if (!$result) {
            Logger::log(
                "Could not insert mapping pair for WP User with ID: {$user['id']}",
                'notice'
            );
        }

        Logger::log(
            'Processed ' . $msgPrefix . ' people, current person id: ' . $user['id'],
            'status'
        );
        $count++;
    }

    wp_suspend_cache_addition(false);
}
