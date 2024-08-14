<?php

declare(strict_types=1);

use Scm\Tools\Logger;

/**
 * Empty media lqip.
 *
 * @return void
 */
function port1649_blemmyae_deployment_ri_004(): void
{
    $query_images_args = [
        'post_type' => 'attachment',
        'post_mime_type' => 'image',
        'post_status' => 'inherit',
        'posts_per_page' => -1,
    ];

    $query_images = new WP_Query($query_images_args);
    $itemsCount = sizeof($query_images->posts);
    $count = 1;

    foreach ($query_images->posts as $post) {
        update_post_meta($post->ID, 'lqip', '');
        Logger::log("Processed $count/$itemsCount images, current image id: $post->ID", 'status');
        $count++;
    }
}
