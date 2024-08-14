<?php

/**
 * @link: https://cra.myjetbrains.com/youtrack/issue/BLEM-604
 */

declare(strict_types=1);

use ReleaseInstructions\Command\CoreCommand as RI;

const BLEM604_EDITORIAL_SC_AWARD_FIELD = "field_62666ecf9fed4";

function blem604_blemmyae_deployment_ri_001(): void
{
    (new RI())->setStatus('blem604_blemmyae_deployment_ri_002', true);
}

function blem604_blemmyae_deployment_ri_002(): void
{
    $scAwards2021Editorials = new WP_Query([
        'post_type' => 'editorial',
        'tax_query' => [
            'relation' => 'OR',
            [
                'taxonomy' => 'brand',
                'field' => 'slug',
                'terms' => 'excellence-awards',
            ],
            [
                'taxonomy' => 'brand',
                'field' => 'slug',
                'terms' => 'leadership-awards',
            ],
            [
                'taxonomy' => 'brand',
                'field' => 'slug',
                'terms' => 'trust-awards',
            ],
        ],
        'post_status' => 'publish',
        'fields' => 'ids',
        'posts_per_page' => -1,
    ]);
    $scAwards2021EditorialsFiltered = array_map(
        static fn($postId) => [
            'id' => $postId,
            'brand' => array_values(
                array_filter(
                    wp_get_post_terms($postId, 'brand') ?? [],
                    static fn($brandTerm) => $brandTerm->slug !== 'sc-awards'
                )
            )[0],
        ],
        $scAwards2021Editorials->posts
    );

    $scAwards2021WinnerTerm = get_term_by('slug', 'winner-2021', 'sc_award');

    foreach ($scAwards2021EditorialsFiltered as $post) {
        $scAwardsTerm = get_term_by('slug', $post['brand']->slug . '-2021', 'sc_award');
        // phpcs:ignore
        update_field(BLEM604_EDITORIAL_SC_AWARD_FIELD, [$scAwardsTerm->term_id, $scAwards2021WinnerTerm->term_id], $post['id']);
        do_action('acf/save_post', $post['id']);
    }

    wp_cache_flush();
}
