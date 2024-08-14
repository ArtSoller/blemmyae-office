<?php

declare(strict_types=1);

use Cra\BlemmyaeApplications\BlemmyaeApplications;

/**
 * Create table to store cross application redirects.
 *
 * @throws Exception
 */
function port2201_blemmyae_deployment_ri_001(): void
{
    global $wpdb;

    $wpdb->query(
        "CREATE TABLE `wp_cross_application_redirects` (
            `post_id` BIGINT(20) UNSIGNED NOT NULL,
            `source` VARCHAR(10) NOT NULL,
            `destination` VARCHAR(10) NOT NULL,
            KEY `post_id` (`post_id`),
            UNIQUE KEY `post_id_source` (`post_id`,`source`)
        ) {$wpdb->get_charset_collate()};"
    );
}

/**
 * Insert existing redirects for SCM -> MSSP.
 *
 * @throws Exception
 */
function port2201_blemmyae_deployment_ri_002(): void
{
    global $wpdb;

    $msspRedirects = [
        666807,
        666763,
        666852,
        666754,
        666839,
        665990,
        666565,
        666482,
        666517,
        666450,
        666536,
        666519,
        666028,
        665893,
        666200,
        518819,
        518821,
        669396,
        667034,
        668982,
        671260,
        670683,
        669297,
        667078,
        667051,
        667102,
        666271,
        666357,
        666246,
        666227,
        666313,
        666886,
        666059,
        665869,
        665956,
    ];

    foreach ($msspRedirects as $postId) {
        $wpdb->insert(
            BlemmyaeApplications::CROSS_APP_REDIRECTS_TABLE,
            ['post_id' => $postId, 'source' => 'scm', 'destination' => 'mssp']
        );
    }
}

/**
 * Insert existing redirects for SCM -> CE2E.
 *
 * @throws Exception
 */
function port2201_blemmyae_deployment_ri_003(): void
{
    global $wpdb;

    $ce2eRedirects = [
        666786,
        666720,
        666743,
        666772,
        666580,
        666330,
        666322,
        665924,
        665865,
        518918,
        669747,
        670905,
        670404,
        666990,
        666347,
        666559,
    ];

    foreach ($ce2eRedirects as $postId) {
        $wpdb->insert(
            BlemmyaeApplications::CROSS_APP_REDIRECTS_TABLE,
            ['post_id' => $postId, 'source' => 'scm', 'destination' => 'ce2e']
        );
    }
}
