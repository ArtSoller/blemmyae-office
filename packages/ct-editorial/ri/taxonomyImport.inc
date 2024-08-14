<?php

declare(strict_types=1);

const TAXONOMY_BRAND = 'brand';
const TAXONOMY_EDITORIAL_TYPE = 'editorial_type';
const TAXONOMY_INDUSTRY = 'industry';
const TAXONOMY_REGION = 'region';
const TAXONOMY_TOPIC = 'topic';
const TAXONOMY_PODCAST_SHOW = 'podcast_show';
const TAXONOMY_SC_AWARD = 'sc_award';

use Scm\Tools\Logger;
use Scm\Tools\Utils;

/**
 * Import all taxonomies.
 */
function importAllTaxonomies(): void
{
    importBrandTaxonomy('/brand.csv');
    importEditorialTypeTaxonomy();
    importIndustryTaxonomy();
    importPodcastShowTaxonomy();
    importRegionTaxonomy('/region.csv');
    importSCAwardTaxonomy('/sc_award/2022-sc-awards-taxonomy.csv');
    // @fixme: Replace with `/topic/topic-new-2023-05-22.csv` after PORT-1815 release.
    importTopicTaxonomy('/topic/topic-new-2023-12-19.csv');
}

/**
 * Import Brand taxonomy.
 *
 * @link https://cra.myjetbrains.com/youtrack/issue/BLEM-6#focus=Comments-4-268.0-0
 * @update https://cra.myjetbrains.com/youtrack/issue/BLEM-258
 */
function importBrandTaxonomy(string $filePath): void
{
    $brandTaxonomy = array_map(
        static fn(array $row) => [
            'category' => $row[0],
            'subcategory' => $row[1],
        ],
        Utils::importCsv(__DIR__ . $filePath)
    );

    // Insert categories and subcategories.
    foreach ($brandTaxonomy as $item) {
        $item['category'] &&
            Utils::insertTaxonomyTerm($item['category'], TAXONOMY_BRAND);
        $item['subcategory'] &&
            Utils::insertTaxonomyTerm($item['subcategory'], TAXONOMY_BRAND, $item['category']);
    }
    wp_cache_flush();
}

/**
 * Import Editorial Type flaxonomy.
 */
function importEditorialTypeTaxonomy(): void
{
    $types = [
        'Analysis',
        'Brief',
        'Feature',
        'Group Podcast',
        'Group Product Test',
        'Intent',
        'Native',
        'News',
        'Perspective',
        'Podcast',
        'Post',
        'Product Test',
        'Research Article',
        'Resource',
        'Scrolling Show',
        'Service',
        'SW Article',
        'Video',
    ];

    foreach ($types as $type) {
        Utils::insertTaxonomyTerm($type, TAXONOMY_EDITORIAL_TYPE);
    }
    wp_cache_flush();
}

/**
 * Import Industry flaxonomy.
 *
 * @link https://cra.myjetbrains.com/youtrack/issue/BLEM-6#focus=Comments-4-246.0-0
 */
function importIndustryTaxonomy(): void
{
    $industryTaxonomy = [
        'Financial Services',
        'Healthcare',
        'Government',
        'Retail ',
        'Education',
        'Non Profit',
        'Energy',
        'Manufacturing',
        'Transportation',
        'Communications',
        'Technology',
        'Defense Industrial Base',
    ];

    foreach ($industryTaxonomy as $industryTerm) {
        Utils::insertTaxonomyTerm($industryTerm, TAXONOMY_INDUSTRY);
    }
    wp_cache_flush();
}

/**
 * Import Podcast Show flaxonomy.
 *
 * @link https://cyberriskalliancecom.sharepoint.com/:x:/s/DevelopmentProjects/EbBh1EIaP8FEt8uzotkXQRcBd3lGPIQiaqsh827FCxlqtw?e=8gecqg
 */
function importPodcastShowTaxonomy(): void
{
    $podcastShowTaxonomy = [
        'Paul\'s Security Weekly',
        'Hack Naked News',
        'Enterprise Security Weekly',
        'Business Security Weekly',
        'Secure Digital Life',
        'Application Security Weekly',
        'Tradecraft Security Weekly',
        'Security and Compliance Weekly',
        'Startup Security Weekly',
        'Security Weekly News',
        'RSA 2020',
        'BH2020',
        'RSAC 2021',
        'The CISO Stories Podcast',
        'Getting the Real Work Done in Cybersecurity',
    ];

    foreach ($podcastShowTaxonomy as $podcastShowTerm) {
        Utils::insertTaxonomyTerm($podcastShowTerm, TAXONOMY_PODCAST_SHOW);
    }
    wp_cache_flush();
}

/**
 * Import Regions taxonomy.
 *
 * @link https://cra.myjetbrains.com/youtrack/issue/BLEM-6#focus=Comments-4-244.0-0
 */
function importRegionTaxonomy(string $filePath): void
{
    $regionTaxonomy = array_map(
        static fn(array $row) => [
            'continent' => $row[0],
            'region' => $row[1],
            'division' => $row[2],
            'state' => $row[3],
        ],
        Utils::importCsv(__DIR__ . $filePath)
    );

    // Insert categories, subcategories and topics.
    foreach ($regionTaxonomy as $item) {
        $item['continent'] &&
            Utils::insertTaxonomyTerm($item['continent'], TAXONOMY_REGION);
        $item['region'] &&
            Utils::insertTaxonomyTerm($item['region'], TAXONOMY_REGION, $item['continent']);
        $item['division'] &&
            Utils::insertTaxonomyTerm($item['division'], TAXONOMY_REGION, $item['region']);
        $item['state'] &&
            Utils::insertTaxonomyTerm($item['state'], TAXONOMY_REGION, $item['division']);
    }
    wp_cache_flush();
}

/**
 * Import SC Award taxonomy.
 *
 * @link https://cra.myjetbrains.com/youtrack/issue/INF-21#focus=Comments-4-2189.0-0
 */
function importSCAwardTaxonomy(string $filePath): void
{
    $topicTaxonomy = array_map(
        static fn(array $row) => [
            'year' => $row[0],
            'category' => $row[1],
            'award' => $row[2],
        ],
        Utils::importCsv(__DIR__ . $filePath)
    );

    // Before
    Logger::log(
        Utils::exportTaxonomy(TAXONOMY_SC_AWARD, true),
        'info'
    );
    Logger::log(
        Utils::exportTaxonomy(TAXONOMY_SC_AWARD, false),
        'info'
    );

    // Insert categories, subcategories and topics.
    foreach ($topicTaxonomy as $item) {
        $item['year'] &&
            Utils::insertTaxonomyTerm($item['year'], TAXONOMY_SC_AWARD);
        $item['category'] &&
            Utils::insertTaxonomyTerm($item['category'], TAXONOMY_SC_AWARD, $item['year']);
        $item['award'] &&
            Utils::insertTaxonomyTerm($item['award'], TAXONOMY_SC_AWARD, $item['category']);
    }

    wp_cache_flush();

    // After
    Logger::log(
        Utils::exportTaxonomy(TAXONOMY_SC_AWARD, true),
        'info'
    );
    Logger::log(
        Utils::exportTaxonomy(TAXONOMY_SC_AWARD, false),
        'info'
    );
}

/**
 * Get name from the hierarchical array.
 *
 * @param array $terms
 *  Terms in hierarchical array.
 * @return string|null
 *  Term name or null.
 */
function getTermNameFromHierarchicalArray(array $terms): string|null
{
    // Last non-null elements in array is term name.
    // Remove nulls.
    $terms = array_filter($terms);

    // Get last element.
    return end($terms) ?? null;
}

/**
 * Import taxonomy from hierarchical array.
 *
 * Import supports only 3 level of hierarchy: 1 - category, 2 - subcategory, 3 - topic.
 * Example of the structure:
 * [
 * ...
 *    [
 *       'category' => '',
 *       'subcategory => '',
 *       'topic' => '',
 *       'subtopic' => '',
 *    ],
 * ...
 * ]
 *
 * @param array $topicTaxonomy
 * @return void
 */
function insertTaxonomyFromArray(array $topicTaxonomy): void
{
    // Insert categories, subcategories, topics and subtopic.
    foreach ($topicTaxonomy as $item) {
        $item['category'] &&
        Utils::insertTaxonomyTerm($item['category'], TAXONOMY_TOPIC);
        $item['subcategory'] &&
        Utils::insertTaxonomyTerm($item['subcategory'], TAXONOMY_TOPIC, $item['category']);
        $item['topic'] &&
        Utils::insertTaxonomyTerm($item['topic'], TAXONOMY_TOPIC, $item['subcategory']);
        $item['subtopic'] &&
        Utils::insertTaxonomyTerm($item['subtopic'], TAXONOMY_TOPIC, $item['topic']);
    }

    wp_cache_flush();
}

/**
 * Export topic from csv to array.
 *
 * CSV should have 3 column: category, subcategory, topic and subtopic.
 *
 * @param string $filePath
 * @return array
 */
function exportTopicFromCsvToArray(string $filePath): array
{
    var_dump(file_get_contents($filePath));
    return array_map(
        static fn(array $row) => [
            'category' => $row[0] ?? null,
            'subcategory' => $row[1] ?? null,
            'topic' => $row[2] ?? null,
            'subtopic' => $row[3] ?? null,
        ],
        Utils::importCsv($filePath)
    );
}

/**
 * Import Topic taxonomy.
 *
 * @link https://cra.myjetbrains.com/youtrack/issue/BLEM-458#focus=Comments-4-1732.0-0
 */
function importTopicTaxonomy(string $filePath): void
{
    // Build topic taxonomy array.
    $topicTaxonomy = exportTopicFromCsvToArray(__DIR__ . $filePath);

    // Before
    taxonomyPrint(TAXONOMY_TOPIC, true);

    // Insert categories, subcategories, topics and subtopic.
    insertTaxonomyFromArray($topicTaxonomy);

    // After
    taxonomyPrint(TAXONOMY_TOPIC, true);
}

/**
 * @param string $taxonomy
 * @param bool $hierarchical
 * @return void
 */
function taxonomyPrint(string $taxonomy, bool $hierarchical): void
{
    Logger::log(
        $hierarchical ? 'Hierarchical mode' : 'Flatten mode',
        'info'
    );
    Logger::log(
        Utils::exportTaxonomy($taxonomy, $hierarchical),
        'info'
    );
    Logger::log(
        Utils::taxonomyCount($taxonomy),
        'info'
    );
}

/**
 * Get posts related to the terms.
 *
 * @param string|int[] $terms
 *  Array with terms ID.
 * @param string $taxonomy
 * @param array $queryArgs
 *
 * @return string[]
 *  Array with post IDs, related to terms.
 */
function getPostIdsRelatedToTerms(array $terms, string $taxonomy = TAXONOMY_TOPIC, array $queryArgs = []): array
{
    // Load all posts with old taxonomy.
    $args = [
        'fields' => 'ids',
        'nopaging' => true,
        'tax_query' => [
            [
                'taxonomy' => $taxonomy,
                'field' => 'id',
                'terms' => $terms,
                'operator' => 'IN',
            ]
        ]
    ];

    // Merge custom args into query.
    $args = array_merge($args, $queryArgs);

    $query = new WP_Query($args);

    return $query->posts ?: [];
}

/**
 * @param string $taxonomy
 * @return array
 */
function countPostsForTaxonomy(string $taxonomy): array
{
    $terms = get_terms([
        'taxonomy' => $taxonomy,
        'fields' => 'all',
        'hide_empty' => false,
    ]);

    if (is_wp_error($terms)) {
        Logger::log($terms->get_error_message(), 'error');
    }

    foreach ($terms as $term) {
        $counter[$term->term_id] = $term->count;
    }

    return $counter ?? [];
}
