<?php

declare(strict_types=1);

use Cra\CtEditorial\EditorialCT;
use ReleaseInstructions\Command\CoreCommand as RI;

function port2371_blemmyae_deployment_ri_001(): void
{
    (new RI())->setStatus('port2371_blemmyae_deployment_ri_002', true);
}

/**
 * All editorials with images.
 */
function port2371_blemmyae_deployment_ri_002(): void
{
    global $wpdb;

    $result = [];
    // phpcs:ignore
    $editorialPosts = $wpdb->get_results("SELECT ID FROM wp_posts WHERE post_content LIKE '%<!-- /wp:image -->%' AND post_type IN ('editorial') AND post_status = 'publish'");

    foreach ($editorialPosts as $editorialPost) {
        $app = get_field(EditorialCT::GROUP_APPLICATION__FIELD_APPLICATION, $editorialPost->ID);
        $slug = get_field(EditorialCT::GROUP_APPLICATION__FIELD_SLUG, $editorialPost->ID);
        $result[] = [$slug, $app->slug];
    }

    $fp = fopen(dirname(__FILE__) . "/port2371/editorials.csv", 'w');

    if ($fp) {
        foreach ($result as $item) {
            fputcsv($fp, $item);
        }

        fclose($fp);
    }
}
