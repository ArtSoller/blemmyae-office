<?php

/**
 * https://cra.myjetbrains.com/youtrack/issue/BLEM-644
 */

declare(strict_types=1);

use Cra\BlemmyaeApplications\BlemmyaeApplications;
use Cra\BlemmyaeApplications\CerberusApps;
use Scm\Entity\CustomPostType;
use Scm\Tools\Logger;
use Scm\Tools\Utils;

/**
 * Add "application" taxonomy terms.
 */
function blem644_administration_ri_012(): void
{
    foreach (BlemmyaeApplications::getListOfAvailableApps() as $app) {
        Utils::insertTaxonomyTerm(
            ucfirst($app),
            BlemmyaeApplications::TAXONOMY,
            '',
            'Taxonomy term for ' . $app . ' CERB application'
        );
    }

    wp_cache_flush();
}

/**
 * Go through landings and select apps.
 */
function blem644_administration_ri_013(): void
{
    $pages = get_posts(
        [
            'post_type' => 'landing',
            'numberposts' => -1,
        ]
    );

    // Map terms and apps.
    // {term_name} => {term_id};
    $mappedTerm = [];

    // App name = application taxonomy slug.
    foreach (BlemmyaeApplications::getListOfAvailableApps() as $appName) {
        if ($term = get_term_by('slug', $appName, BlemmyaeApplications::TAXONOMY)) {
            $mappedTerm[$appName] = $term->term_id;
        }
    }

    $countItems = $count = count($pages);

    Logger::log("Items left to process $countItems:", 'info');

    foreach ($pages as $page) {
        // Get app name & push this data into field.
        $app = BlemmyaeApplications::getAppsNameByLandingPath($page->post_name);
        update_field(CerberusApps::APPLICATION_FIELD, $mappedTerm[$app], $page->ID);

        // Logs.
        $msg = sprintf(
            "[%s/%s] Landing #%s: %s Apps has been successfully updated to %s",
            $count--,
            $countItems,
            $page->ID,
            $page->post_title,
            $app
        );

        Logger::log($msg, !empty($app) ? 'success' : 'info');
    }

    wp_cache_flush();
}

/**
 * Load all landings w/o application terms.
 *
 * Just for logs.
 */
function blem644_administration_ri_014(): void
{
    $query = new WP_Query([
        'post_type' => 'landing',
        'posts_per_page' => -1,
        'meta_query' => [
            [
                'key' => 'application',
                'compare' => 'NOT EXISTS',
            ],
        ],
    ]);

    // If we still have not assigned pages => push list of landings into logs
    // with warnings.
    if ($query->have_posts()) {
        foreach ($query->get_posts() as $page) {
            // Logs.
            $msg = sprintf(
                "Landing #%s - %s - APPLICATION FIELD IS EMPTY!",
                $page->ID,
                $page->post_title,
            );

            Logger::log($msg, 'warning');
        }

        // Report error.
        Logger::log('All landings should have application!', 'error');
    }
}
