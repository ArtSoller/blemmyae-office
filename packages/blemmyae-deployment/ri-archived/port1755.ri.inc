<?php

declare(strict_types=1);

use Cra\BlemmyaeApplications\CerberusApps;
use Scm\Tools\Logger;

/**
 * Flatten application field value for some learnings
 *
 * @return void
 */
function port1755_blemmyae_deployment_ri_001(): void
{
    $wpQueryArgs = [
        'post_type' => 'learning',
        'posts_per_page' => -1,
    ];

    $wpQuery = new WP_Query($wpQueryArgs);
    $learningsWithArrayApplication = array_filter(
        $wpQuery->posts,
        fn($learning) => is_array(get_field(CerberusApps::APPLICATION_FIELD, $learning->ID))
    );
    foreach ($learningsWithArrayApplication as $learning) {
        $applicationFieldValue = get_field(CerberusApps::APPLICATION_FIELD, $learning->ID)[0];
        update_field(CerberusApps::APPLICATION_FIELD, $applicationFieldValue, $learning->ID);
        Logger::log(
            $applicationFieldValue->name . " application field value flattened for post " . $learning->ID,
            'info'
        );
    }
}
