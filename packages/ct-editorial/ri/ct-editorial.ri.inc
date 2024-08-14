<?php

/**
 * @link: https://cra.myjetbrains.com/youtrack/issue/BLEM-9
 */

declare(strict_types=1);

require_once 'taxonomyImport.inc';

/**
 * Re-import all taxonomies.
 */
function ct_editorial_ri_075(): void
{
    importAllTaxonomies();
    wp_cache_flush();
    // Run init hook again so taxonomies imported above get registered.
    @do_action('init');
}

