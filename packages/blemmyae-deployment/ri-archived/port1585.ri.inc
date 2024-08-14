<?php

declare(strict_types=1);

use Cra\CtLearning\Plugin;

/**
 * Create persons from speaker card in event body.
 *
 * @return void
 */
function port1585_blemmyae_deployment_ri_002(): void
{
    global $wpdb;

    // phpcs:ignore
    $speakerPosts = $wpdb->get_results("SELECT ID FROM wp_posts WHERE post_content LIKE '%<!-- /wp:cra/webcast-speaker -->%' AND post_type = 'learning' AND post_status = 'publish'");

    foreach ($speakerPosts as $speakerPost) {
        Plugin::convertGutenbergSpeakerBlockToPersonReference($speakerPost->ID);
    }
}
