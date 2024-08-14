<?php

declare(strict_types=1);

use Cra\BlemmyaeDeployment\A9SMigration;
use Cra\BlemmyaeDeployment\A9SMigration\Mapper\Vendor\Alert\Organizer as AlertOrganizer;
use Cra\BlemmyaeDeployment\A9SMigration\Mapper\Vendor\Ce2e\Organizer as Ce2eOrganizer;
use Cra\BlemmyaeDeployment\A9SMigration\MigrationHandler;
use Cra\BlemmyaeDeployment\A9SMigration\MigrationMappingTable;
use ReleaseInstructions\Command\CoreCommand as RI;
use Scm\Tools\Utils;

function a9s145_migration_ri_001(): void
{
    $migrationMapping = new MigrationMappingTable();
    $migrationMapping->setupMappingsTable();
    $migrationMapping->addEntityTypeToMappingsTable();
    (new RI())->setStatus('a9s145_migration_ri_002', true);
    (new RI())->setStatus('a9s145_migration_ri_003', true);
}

/**
 * Migrate MSSP Alert Organizers to Blemmyae Company Profile entity.
 *
 * @return void
 * @throws Exception
 */
function a9s145_migration_ri_002(): void
{
    new A9SMigration(
        MigrationHandler::VENDOR__ALERT,
        AlertOrganizer::TYPE,
        a9s145_input_data(MigrationHandler::VENDOR__ALERT, AlertOrganizer::TYPE),
        true
    );
}

/**
 * Migrate CE2E Organizers to Blemmyae Company Profile entity.
 *
 * @return void
 * @throws Exception
 */
function a9s145_migration_ri_003(): void
{
    new A9SMigration(
        MigrationHandler::VENDOR__CE2E,
        Ce2eOrganizer::TYPE,
        a9s145_input_data(MigrationHandler::VENDOR__CE2E, Ce2eOrganizer::TYPE),
        true
    );
}

/**
 * @param string $vendor
 * @param string $organizerType
 * @return array
 * @throws Exception
 */
function a9s145_input_data(string $vendor, string $organizerType): array
{
    // Fix memory leak.
    wp_suspend_cache_addition(true);

    global $wpdb;

    $utils = new Utils();

    $dbName = MigrationHandler::dbNameByVendor($vendor);
    if (!$dbName) {
        return [];
    }

    $utils->switchDB($dbName);
    $organizerIds = array_column($wpdb->get_results(
        "SELECT * FROM `wp_posts` WHERE post_type='tribe_organizer'"
    ), 'ID');

    $organizers = [];
    $mObject = new A9SMigration\MigrationObject();
    foreach ($organizerIds as $organizerId) {
        $organizers[$organizerId] = $mObject::construct([
            'event' => 'create',
            'vendor' => $vendor,
            'objectType' => $organizerType,
            'uuid' => $organizerId,
            'timestamp' => time(),
            'object' => [
                'post' => get_post($organizerId),
                'meta' => get_post_meta($organizerId),
            ]
        ]);
    }

    $utils->switchDBToDefault();

    wp_suspend_cache_addition(false);

    return $organizers;
}
