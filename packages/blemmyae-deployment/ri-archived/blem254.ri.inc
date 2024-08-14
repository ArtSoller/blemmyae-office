<?php

/**
 * @link: https://cra.myjetbrains.com/youtrack/issue/BLEM-254
 */

use Scm\Tools\Logger;

/**
 * Publishes Guest Authors.
 */
function blem254_blemmyae_deployment_ri_001(): void
{
    $posts = get_posts(
        [
            'post_type' => 'guest-author',
            'post_status' => 'draft',
            'numberposts' => -1,
        ]
    );

    $countAll = $count = count($posts);
    foreach ($posts as $post) {
        $count--;
        $msgPrefix = '[' . $countAll . '/' . $count . '] ';

        wp_publish_post($post);
        Logger::log("$msgPrefix`$post->post_title` ($post->ID) was published", 'success');
    }

    wp_cache_flush();
}
