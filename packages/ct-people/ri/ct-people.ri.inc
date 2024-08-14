<?php

/**
 * @link: https://cra.myjetbrains.com/youtrack/issue/BLEM-9
 */

/** @noinspection PhpUnused */

declare(strict_types=1);

const TAXONOMY__CSC_PEOPLE_TYPE = 'csc_people_type';

use Cra\CtPeople\PeopleCT;
use Scm\Entity\CustomPostType;
use Scm\Tools\Utils;

/**
 * Import Swoogo Speaker Type taxonomy terms.
 */
function ct_people_ri_019(): void
{
    $swoogoSpeakerTypeTaxonomy = array_map(
        fn(array $row) => [
            'type' => $row[0],
            'subtype' => $row[1],
        ],
        Utils::importCsv(__DIR__ . '/swoogo_speaker_type.csv')
    );

    // Insert types and subtypes.
    foreach ($swoogoSpeakerTypeTaxonomy as $item) {
        Utils::insertTaxonomyTerm($item['type'], PeopleCT::TAXONOMY__SWOOGO_SPEAKER_TYPE);
        Utils::insertTaxonomyTerm($item['subtype'], PeopleCT::TAXONOMY__SWOOGO_SPEAKER_TYPE, $item['type']);
    }
    wp_cache_flush();
}

/**
 * Unregister 'Swoogo Speaker Event Region' taxonomy and all its terms.
 * This taxonomy is replaced by 'Community Region'.
 *
 * @link https://cra.myjetbrains.com/youtrack/issue/CISO-29
 */
function ct_people_ri_021(): void
{
    $taxonomy = 'swoogo_speaker_event_region';
    $terms = array_values(acf_get_taxonomy_terms([$taxonomy]))[0];
    foreach ($terms as $termName) {
        $term = get_term_by('name', $termName, $taxonomy);
        if ($term instanceof WP_Term) {
            wp_delete_term($term->term_id, $taxonomy);
        }
    }
    cptui_delete_taxonomy($taxonomy);  // @phpstan-ignore-line
}

/**
 * Import People Type flaxonomy.
 */
function ct_people_ri_023(): void
{
    $items = [
        [
            'type' => 'Author',
            'subtype' => '',
        ],
        [
            'type' => 'Industry Figure',
            'subtype' => '',
        ],
        [
            'type' => 'Industry Figure',
            'subtype' => 'Community Director',
        ],
        [
            'type' => 'Industry Figure',
            'subtype' => 'Co-Chair',
        ],
        [
            'type' => 'Industry Figure',
            'subtype' => 'Leadership Board',
        ],
        [
            'type' => 'Speaker',
            'subtype' => 'Host',
        ],
        [
            'type' => 'Speaker',
            'subtype' => 'Guest',
        ],
    ];

    foreach ($items as $item) {
        Utils::insertTaxonomyTerm($item['type'], PeopleCT::TAXONOMY__PEOPLE_TYPE);
        Utils::insertTaxonomyTerm($item['subtype'], PeopleCT::TAXONOMY__PEOPLE_TYPE, $item['type']);
    }
    wp_cache_flush();
}

/**
 * Import CSC People Type taxonomy.
 */
function ct_people_ri_026(): void
{
    $items = [
        'CSC Team',
        'Executive Committee',
    ];

    foreach ($items as $item) {
        Utils::insertTaxonomyTerm($item, TAXONOMY__CSC_PEOPLE_TYPE);
    }
    wp_cache_flush();
}
