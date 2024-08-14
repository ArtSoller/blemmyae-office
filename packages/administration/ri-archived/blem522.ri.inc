<?php

/**
 * @link: https://cra.myjetbrains.com/youtrack/issue/BLEM-522
 */

declare(strict_types=1);

use Scm\Entity\CustomPostType;
use Scm\Entity\Flag;
use Scm\Tools\Utils;

/**
 * Add "Flag" taxonomy terms.
 */
function blem522_administration_ri_002(): void
{
    $terms = [
        [
            'name' => Flag::TAXONOMY_TERM__SHOW_IN_FEEDS,
            // phpcs:ignore
            'description' => 'If the content should appear in various feeds on the site (e.g. ppworks episode index page)',
        ],
    ];

    foreach ($terms as $term) {
        Utils::insertTaxonomyTerm($term['name'], Flag::TAXONOMY, '', $term['description']);
    }

    wp_cache_flush();
}
