<?php

/**
 * @link: https://cra.myjetbrains.com/youtrack/issue/BLEM-577
 */

declare(strict_types=1);

use ReleaseInstructions\Command\CoreCommand as RI;
use Scm\Tools\Logger;

function blem577_blemmyae_deployment_ri_001(): void
{
    (new RI())->setStatus('blem577_blemmyae_deployment_ri_002', true);
}

function blem577_blemmyae_deployment_ri_002(): void
{
    // phpcs:ignore
    $postTypesToProcess = ['product-test', 'group-product-test', 'podcast', 'group-podcast', 'scrolling-show', 'video', 'brief'];

    $wpQuery = new WP_Query([
        'post_type' => 'editorial',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'tax_query' => [
            [
                'taxonomy' => 'editorial_type',
                'field' => 'slug',
                'operator' => 'IN',
                'terms' => $postTypesToProcess,
            ],
        ],
        'fields' => 'ids',
    ]);

    $wpQueryPosts = $wpQuery->posts;

    Logger::log('Number of posts found - ' . $wpQuery->found_posts, 'info');

    foreach ($wpQueryPosts as $postId) {
        update_field('show_featured_image', 0, $postId);
        Logger::log('Setting show featured image field value to false for ' . $postId, 'info');
    }

    wp_cache_flush();
}
