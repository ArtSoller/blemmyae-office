<?php

/**
 * https://cra.myjetbrains.com/youtrack/issue/PORT-3164
 */

declare(strict_types=1);

use Cra\BlemmyaeApplications\BlemmyaeApplications;
use Scm\Tools\Utils;

/**
 * Add "application" taxonomy terms.
 */
function port3164_blemmyae_deployment_ri_001(): void
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
