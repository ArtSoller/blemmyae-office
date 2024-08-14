<?php

/**
 * @link: https://cra.myjetbrains.com/youtrack/issue/BLEM-9
 */

declare(strict_types=1);

const TAXONOMY_LEARNING_TYPE = 'learning_type';

const TAXONOMY_LEARNING_VENDOR_TYPE = 'learning_vendor_type';

const TAXONOMY_COMMUNITY_REGION = 'community_region';

use Scm\Tools\Utils;

/**
 * Import Community Region Taxonomy.
 *
 * @link https://cra.myjetbrains.com/youtrack/issue/CISO-3#focus=Comments-4-2391.0-0
 */
function ct_learning_ri_015(): void
{
    $communityRegionTaxonomy = array_map(
        static fn(array $row) => [
            'region' => $row[0],
            'state' => $row[1],
            'city' => $row[2],
        ],
        Utils::importCsv(__DIR__ . '/community_region.csv')
    );

    // Insert categories, subcategories and topics.
    foreach ($communityRegionTaxonomy as $item) {
        Utils::insertTaxonomyTerm($item['region'], TAXONOMY_COMMUNITY_REGION);
        Utils::insertTaxonomyTerm($item['state'], TAXONOMY_COMMUNITY_REGION, $item['region']);
        Utils::insertTaxonomyTerm($item['city'], TAXONOMY_COMMUNITY_REGION, $item['state']);
    }
    wp_cache_flush();
}

/**
 * Removes 'SwoogoSession' and 'SwoogoEvent' terms from 'learning vendor type' taxonomy.
 *
 * @link https://cra.myjetbrains.com/youtrack/issue/CISO-29
 */
function ct_learning_ri_020(): void
{
    $taxonomy = 'learning_vendor_type';
    // Array of terms to delete.
    $vendorTypeTerms = ['SwoogoEvent', 'SwoogoSession'];
    foreach ($vendorTypeTerms as $termName) {
        $term = get_term_by('name', $termName, $taxonomy);
        if (!is_wp_error($term) && !empty($term)) {
            wp_delete_term( $term->term_id, $taxonomy);
        }
    }
}

/**
 * Import Learning Vendor Type taxonomy terms.
 */
function ct_learning_ri_029(): void
{
    $types = [
        'Intrado',
        'GoToWebinar',
        'Swoogo',
        'YouTube',
        'CSC Member Portal',
        'Cybersecurity Conference',
    ];

    foreach ($types as $type) {
        Utils::insertTaxonomyTerm($type, TAXONOMY_LEARNING_VENDOR_TYPE);
    }
    wp_cache_flush();
    // Run init hook again so taxonomies imported above get registered.
    @do_action('init');
}

/**
 * Import Learning Type taxonomy.
 */
function ct_learning_ri_028(): void
{
    $learningTypeTaxonomy = array_map(
        static fn(array $row) => [
            'category' => $row[0],
            'subcategory' => $row[1],
        ],
        Utils::importCsv(__DIR__ . '/learning_type.csv')
    );

    // Insert categories and subcategories.
    foreach ($learningTypeTaxonomy as $item) {
        Utils::insertTaxonomyTerm($item['category'], TAXONOMY_LEARNING_TYPE);
        Utils::insertTaxonomyTerm($item['subcategory'], TAXONOMY_LEARNING_TYPE, $item['category']);
    }
    wp_cache_flush();
}
