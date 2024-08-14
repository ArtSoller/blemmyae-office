<?php

/**
 * @link: https://cra.myjetbrains.com/youtrack/issue/BLEM-9
 * @noinspection PhpUnused
 */

declare(strict_types=1);

const TAXONOMY_COMPANY_PROFILE_TYPE = 'company_profile_type';

use Scm\Tools\Utils;

/**
 * Import Company Profile Type flaxonomy.
 */
function ct_company_profile_ri_004(): void
{
    $types = [
        'Client',
        'Vendor',
    ];

    foreach ($types as $type) {
        Utils::insertTaxonomyTerm($type, TAXONOMY_COMPANY_PROFILE_TYPE);
    }
    wp_cache_flush();
}
