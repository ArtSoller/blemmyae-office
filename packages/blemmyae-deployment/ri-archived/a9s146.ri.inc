<?php

declare(strict_types=1);

use Cra\BlemmyaeDeployment\A9SMigration;
use Cra\BlemmyaeDeployment\A9SMigration\Mapper\Vendor\Alert\Event as AlertEvent;
use Cra\BlemmyaeDeployment\A9SMigration\Mapper\Vendor\Ce2e\Event as Ce2eEvent;
use Cra\BlemmyaeDeployment\A9SMigration\MigrationHandler;
use Cra\CtLearning\LearningCT;
use ReleaseInstructions\Command\CoreCommand as RI;
use Scm\Tools\Utils;

function a9s146_migration_ri_001(): void
{
    (new RI())->setStatus('a9s146_migration_ri_002', true);
    (new RI())->setStatus('a9s146_migration_ri_003', true);
}

/**
 * @throws Exception
 */
function a9s146_migration_ri_004(): void
{
    $terms = [
        'Cybersecurity Conference'
    ];

    foreach ($terms as $term) {
        $newTerm = wp_create_term($term, LearningCT::TAXONOMY__LEARNING_TYPE);

        if ($newTerm instanceof \WP_Error) {
            throw new Exception("Error while creating new term $term");
        }
    }

    wp_cache_flush();
}

/**
 * @throws Exception
 */
function a9s146_migration_ri_005(): void
{
    $terms = [
        LearningCT::VENDOR_TYPE__CE2E,
        LearningCT::VENDOR_TYPE__MSSP,
    ];

    foreach ($terms as $term) {
        $newTerm = wp_create_term($term, LearningCT::TAXONOMY__VENDOR_TYPE);

        if ($newTerm instanceof \WP_Error) {
            throw new Exception("Error while creating new term $term");
        }
    }

    wp_cache_flush();
}

/**
 * Migrate MSSP Alert Events to Blemmyae Events (Learnings) entity.
 *
 * @return void
 * @throws Exception
 */
function a9s146_migration_ri_002(): void
{
    new A9SMigration(
        MigrationHandler::VENDOR__ALERT,
        AlertEvent::TYPE,
        a9s146_input_data(MigrationHandler::VENDOR__ALERT, AlertEvent::TYPE),
        true
    );
}

/**
 * Migrate CE2E Events to Blemmyae Events (Learnings) entity.
 *
 * @return void
 * @throws Exception
 */
function a9s146_migration_ri_003(): void
{
    new A9SMigration(
        MigrationHandler::VENDOR__CE2E,
        Ce2eEvent::TYPE,
        a9s146_input_data(MigrationHandler::VENDOR__CE2E, Ce2eEvent::TYPE),
        true
    );
}

/**
 * @param string $vendor
 * @param string $eventType
 * @return array
 * @throws Exception
 */
function a9s146_input_data(string $vendor, string $eventType): array
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
    $venueIds = array_column($wpdb->get_results(
        "SELECT * FROM `wp_posts` WHERE post_type='tribe_venue'"
    ), 'ID');

    $venueObjects = [];
    foreach ($venueIds as $venueId) {
        $venueObjects[$venueId] = [
            'post' => get_post($venueId),
            'meta' => get_post_meta($venueId),
        ];
    }

    $eventIds = array_column(
        $wpdb->get_results(
            "SELECT * FROM `wp_posts` WHERE post_type='tribe_events' AND post_status!='auto-draft'"
        ),
        'ID'
    );

    $eventObjects = [];
    $mObject = new A9SMigration\MigrationObject();
    foreach ($eventIds as $eventId) {
        $postMeta = get_post_meta($eventId);
        $venue = [];
        if (!empty($postMeta["_EventVenueID"])) {
            $venue = $venueObjects[$postMeta["_EventVenueID"][0]] ?? [];
        }

        $eventObjects[$eventId] = $mObject::construct([
            'event' => 'create',
            'vendor' => $vendor,
            'objectType' => $eventType,
            'uuid' => $eventId,
            'timestamp' => time(),
            'object' => [
                'post' => get_post($eventId),
                'meta' => $postMeta,
                'venue' => $venue,
            ]
        ]);
    }

    $utils->switchDBToDefault();

    wp_suspend_cache_addition(false);

    return $eventObjects;
}
