<?php

/**
 * @link: https://cra.myjetbrains.com/youtrack/issue/BLEM-630
 */

declare(strict_types=1);

use ReleaseInstructions\Command\CoreCommand as RI;
use Scm\Tools\Logger;

function int262_blemmyae_deployment_ri_001(): void
{
    (new RI())->setStatus('int262_blemmyae_deployment_ri_002', true);
}

/**
 * Removes excessive media items attached to whitepapers
 * from s3 bucket and from media library
 *
 * @return void
 */
function int262_blemmyae_deployment_ri_002(): void
{
    $draftWhitepapersWpQuery = new WP_Query([
        'post_type' => 'whitepaper',
        'post_status' => 'draft',
        'posts_per_page' => -1,
    ]);

    $publishWhitepapersWpQuery = new WP_Query([
        'post_type' => 'whitepaper',
        'post_status' => 'publish',
        'posts_per_page' => -1,
    ]);

    $draftWhitepapers = $draftWhitepapersWpQuery->get_posts();
    // phpcs:ignore
    $publishWhitepapersTitles = array_map(fn(WP_Post $post) => $post->post_title, $publishWhitepapersWpQuery->get_posts());
    $draftStaleWhitepapers = array_filter(
        $draftWhitepapers,
        // 604800 - number of seconds in a week. Filter out draft whitepapers that are fresh
        fn($whitepaper) => (time() - strtotime($whitepaper->post_modified_gmt)) > 604800
    );

    // We want to process draft whitepapers with published duplicates only
    $draftWhitepapersWithPublishedDuplicates = array_filter(
        $draftStaleWhitepapers,
        fn($whitepaper) => in_array($whitepaper->post_title, $publishWhitepapersTitles)
    );

    foreach ($draftWhitepapersWithPublishedDuplicates as $draftStaleWhitepaper) {
        $whitepaperId = $draftStaleWhitepaper->ID;
        Logger::log("Trashing whitepaper $whitepaperId", 'info');
        $updatePostResult = wp_update_post(
            [
                'ID' => $whitepaperId,
                'post_status' => 'trash',
            ]
        );

        $updatePostResult ?
            Logger::log("Whitepaper $whitepaperId trashed", 'info') :
            Logger::log("Error trashing whitepaper $whitepaperId", 'warn');
        Logger::log("---------------------------------", 'info');
    }

    wp_cache_flush();
}
