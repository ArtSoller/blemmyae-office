<?php

declare(strict_types=1);

use Cra\BlemmyaeApplications\BlemmyaeApplications;
use Cra\BlemmyaeDeployment\A9SMigration;
use Cra\BlemmyaeDeployment\SetupMappingsTable;
use ReleaseInstructions\Command\CoreCommand as RI;
use Scm\Tools\Logger;
use Scm\Tools\Utils;

function a9s132_migration_ri_001(): void
{
    (new RI())->setStatus('a9s132_migration_ri_002', true);
    (new RI())->setStatus('a9s132_migration_ri_003', true);
}

/**
 * Migrate MSSP Alert media entity to Blemmyae media.
 *
 * @return void
 * @throws Exception
 */
function a9s132_migration_ri_002(): void
{
    // Fix memory leak.
    wp_suspend_cache_addition(true);

    $queryArgs = [
        'post_type' => 'attachment',
        'post_status' => 'inherit',
        'posts_per_page' => -1,
    ];

    $utils = new Utils();

    $utils->switchDB(A9SMigration::ALERT_DB_NAME);

    $queryMedia = new WP_Query($queryArgs);

    $utils->switchDBToDefault();

    $itemsCount = $queryMedia->post_count;
    $count = 1;

    $setupMappingsTable = new SetupMappingsTable();
    $setupMappingsTable->setupTable(
        A9SMigration::ALERT_MEDIA_MAPPING_TABLE,
        A9SMigration::ALERT_ID_COLUMN,
        A9SMigration::BLEMMYAE_ID_COLUMN
    );

    foreach ($queryMedia->posts as $media) {
        $msgPrefix = '[' . $count . '/' . $itemsCount . ']';

        $id = $media->ID;
        $newId = A9SMigration::getMapping('media', BlemmyaeApplications::MSSP, $id);
        $status = !empty($newId) ? 'skipping' : 'creating';

        if (is_array($newId) && !empty($newId)) {
            $newId = array_shift($newId);
        }

        Logger::log("$msgPrefix: $id $status...", 'status');

        if ($newId) {
            $count++;
            continue;
        }

        // Import file to database and save alert_id -> blem_id media mapping.
        $import_response = A9SMigration::createAttachment(
            $media,
            'alert',
            A9SMigration::ALERT_MEDIA_MAPPING_TABLE,
            A9SMigration::ALERT_ID_COLUMN
        );

        if ($import_response !== true) {
            Logger::log($msgPrefix . 'Error: ', 'notice');
            print_r($import_response);
        }

        Logger::log(
            'Processed ' . $msgPrefix . ' media, current media id: ' . $media->ID,
            'status'
        );
        $count++;
    }

    wp_suspend_cache_addition(false);
}

/**
 * Migrate Channel E2E media entity to Blemmyae media.
 *
 * @return void
 * @throws Exception
 */
function a9s132_migration_ri_003(): void
{
    // Fix memory leak.
    wp_suspend_cache_addition(true);

    $queryArgs = [
        'post_type' => 'attachment',
        'post_status' => 'inherit',
        'posts_per_page' => -1,
    ];

    $utils = new Utils();

    $utils->switchDB(A9SMigration::CE2E_DB_NAME);

    $queryMedia = new WP_Query($queryArgs);

    $utils->switchDBToDefault();

    $itemsCount = $queryMedia->post_count;
    $count = 1;

    $setupMappingsTable = new SetupMappingsTable();
    $setupMappingsTable->setupTable(
        A9SMigration::CE2E_MEDIA_MAPPING_TABLE,
        A9SMigration::CE2E_ID_COLUMN,
        A9SMigration::BLEMMYAE_ID_COLUMN
    );

    foreach ($queryMedia->posts as $media) {
        $msgPrefix = '[' . $count . '/' . $itemsCount . ']';

        $id = $media->ID;
        $newId = A9SMigration::getMapping('media', BlemmyaeApplications::CE2E, $id);
        $status = !empty($newId) ? 'skipping' : 'creating';

        if (is_array($newId) && !empty($newId)) {
            $newId = array_shift($newId);
        }

        Logger::log("$msgPrefix: $id $status...", 'status');

        if ($newId) {
            $count++;
            continue;
        }

        // Import file to database and save ce2e_id -> blem_id media mapping.
        $import_response = A9SMigration::createAttachment(
            $media,
            'ce2e',
            A9SMigration::CE2E_MEDIA_MAPPING_TABLE,
            A9SMigration::CE2E_ID_COLUMN
        );

        if ($import_response !== true) {
            Logger::log($msgPrefix . 'Error: ', 'notice');
            print_r($import_response);
        }

        Logger::log(
            'Processed ' . $msgPrefix . ' media, current media id: ' . $media->ID,
            'status'
        );
        $count++;
    }

    wp_suspend_cache_addition(false);
}
