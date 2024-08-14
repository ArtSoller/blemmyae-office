<?php

/**
 * @link: https://cra.myjetbrains.com/youtrack/issue/BLEM-9
 */

declare(strict_types=1);

const TAXONOMY_LANDING_TYPE = 'landing_type';

use Cra\CtLanding\LandingCT;
use Scm\Tools\Logger;
use Scm\Tools\Utils;

/**
 * Import Landing type internal usage taxonomy.
 */
function ct_landing_ri_004(): void
{
    $landingTaxonomy = array_map(
        static fn(array $row) => [
            'category' => $row[0],
            'subcategory' => $row[1],
        ],
        Utils::importCsv(__DIR__ . '/landing_type.csv')
    );

    // Insert categories, subcategories and topics.
    foreach ($landingTaxonomy as $item) {
        Utils::insertTaxonomyTerm($item['category'], TAXONOMY_LANDING_TYPE);
        Utils::insertTaxonomyTerm($item['subcategory'], TAXONOMY_LANDING_TYPE, $item['category']);
    }
    wp_cache_flush();
}

/**
 * Clean-ups body field.
 */
function ct_landing_ri_006(): void
{
    $query = new WP_Query(['post_type' => LandingCT::POST_TYPE, 'nopaging' => true]);
    while ($query->have_posts()) {
        $post = $query->next_post();
        $currentPost = $query->current_post + 1;
        Logger::log("Processing $currentPost/$query->post_count.", 'info');
        $post->post_content = '';
        $post->post_excerpt = '';
        wp_update_post($post);
        $result = wp_update_post($post);
        if ($result instanceof WP_Error) {
            Logger::log("[$post->post_title]: " . implode(PHP_EOL, $result->errors), 'error');
            continue;
        }
        Logger::log("Updated post $post->post_title", 'success');
    }
}
