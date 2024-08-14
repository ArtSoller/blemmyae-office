<?php

declare(strict_types=1);

use Cra\BlemmyaeApplications\CerberusApps;
use Scm\Tools\Logger;

const BRAND_FIELD = 'field_60e7d2c7c2ab5';

/**
 * Map brand->application for events.
 *
 * @return void
 */
function port1642_blemmyae_deployment_ri_002(): void
{
    $application_mapping = [
        // SC Media -> SCM.
        '72316' => '74238',
        // Cybersecurity Collaborative -> CSC.
        '72321' => '74240',
        // Cybersecurity Collaboration Forum -> CISO.
        '72320' => '74239',
        // Security Weekly -> SCM.
        '72317' => '74238',
        // SC Events -> SCM.
        '72323' => '74238',
    ];

    $events = get_posts([
        'post_type' => 'learning',
        'numberposts' => -1,
        // Somewhy 'any' doesn't work.
        'post_status' => ['publish', 'archive', 'draft', 'private'],
    ]);

    $count = 1;
    $events_count = sizeof($events);

    foreach ($events as $event) {
        // Return brand, if brand does not exist => return SCM.
        $brand = get_field(BRAND_FIELD, $event->ID, false) ?: [];
        $brand = reset($brand);

        // Get application id.
        $application = $application_mapping[$brand] ?? '74238';

        // Update application field.
        update_field(CerberusApps::APPLICATION_FIELD, $application, $event->ID);

        // Logs.
        Logger::log("Updated application for event: $event->ID", 'success');
        Logger::log("Processed $count events out of $events_count", 'status');

        $count++;
    }
}
