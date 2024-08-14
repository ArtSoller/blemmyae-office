<?php

declare(strict_types=1);

use Cra\BlemmyaeDeployment\A9SMigration;
use Cra\CtEditorial\EditorialCT;
use ReleaseInstructions\Command\CoreCommand as RI;
use Scm\Tools\Logger;

const PORT2077_META_KEY = 'vendor';
const PORT2077_META_VALUE = 'a:1:{i:0;s:8:\"innodata\";}';
function port2077_migration_ri_001(): void
{
    (new RI())->setStatus('port2077_migration_ri_002', true);
}

/**
 * Add rel="nofollow" to source links inside content of Blemmyae Editorial Briefs with vendor Innodata.
 *
 * @throws Exception
 */
function port2077_migration_ri_002(): void
{
    // Update Editorial content.
    port2077_update_posts(
        EditorialCT::POST_TYPE,
        PORT2077_META_KEY,
        PORT2077_META_VALUE
    );
}

/**
 * @param string $postType
 * @param string $key
 * @param string $value
 * @return void
 */
function port2077_update_posts(
    string $postType,
    string $key,
    string $value
): void {
    wp_suspend_cache_addition(true);
    global $wpdb;

    $posts = $wpdb->get_results(
        "SELECT wp_posts.ID, wp_posts.post_content FROM wp_posts
                    INNER JOIN wp_postmeta ON wp_posts.ID = wp_postmeta.post_id
                WHERE wp_posts.post_type='$postType' AND wp_postmeta.meta_key='$key'
                AND wp_postmeta.meta_value='$value'"
    );

    if (empty($posts)) {
        Logger::log("Unable to get posts for post type $postType, meta_key $key and meta_value $value", 'error');
    }

    $itemsCount = count($posts);
    $count = 1;

    foreach ($posts as $post) {
        $msgPrefix = '[' . $count . '/' . $itemsCount . ']';
        Logger::log("$msgPrefix: $post->ID updating...", 'status');
        $count++;

        $content = A9SMigration::addAttrRelToHyperlink($post->post_content);

        if ($content === $post->post_content) {
            continue;
        } else {
            // Update Post Content.
            wp_update_post([
                'ID' => $post->ID,
                'post_content' => $content
            ]);

            wp_cache_flush();
        }
    }

    wp_suspend_cache_addition(false);
}
