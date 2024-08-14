<?php

declare(strict_types=1);

use ReleaseInstructions\Command\CoreCommand as RI;
use Scm\Tools\Logger;

function port2275_blemmyae_deployment_ri_001(): void
{
    (new RI())->setStatus('port2275_blemmyae_deployment_ri_002', true);
}

/**
 * Re-save posts.
 */
function port2275_blemmyae_deployment_ri_002(): void
{
    global $wpdb;

    $postsIDs = $wpdb->get_results(
        "SELECT ID FROM wp_posts WHERE post_author = 38390 AND post_type='learning' AND post_status = 'archive'",
        ARRAY_N
    );

    $size = count($postsIDs);
    $count = 1;

    foreach ($postsIDs as $postID) {
        $msgPrefix = '[' . $count . '/' . $size . ']';

        $postID = array_shift($postID);
        do_action('acf/save_post', $postID);

        Logger::log("$msgPrefix posts saved, post ID: $postID", 'status');
        $count++;
    }
}
