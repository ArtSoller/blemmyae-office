<?php

/**
 * Release Instructions related to BLEM-20 & BLEM-364 issues.
 *
 * @link: https://cra.myjetbrains.com/youtrack/issue/BLEM-20
 * @link: https://cra.myjetbrains.com/youtrack/issue/BLEM-364
 */

declare(strict_types=1);

use Cra\BlemmyaeDeployment\MigratedPostsRedirectImporter;

/**
 * Import redirects for migrated posts.
 */
function blem20_blemmyae_deployment_ri_001(): void
{
    $importer = new MigratedPostsRedirectImporter();
    $importer->execute(__DIR__ . '/blem20/WP_Query-Export-2021-July-21-1047.csv');
}
