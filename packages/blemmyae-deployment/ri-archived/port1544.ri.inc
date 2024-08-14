<?php

/**
 * @link: https://cra.myjetbrains.com/youtrack/issue/PORT-1544/Investigate-Broken-Author-Links
 */

declare(strict_types=1);

use Cra\CtEditorial\EditorialCT;
use ReleaseInstructions\Command\CoreCommand as RI;
use Scm\Tools\Logger;
use Scm\Tools\Utils;

function port1544_blemmyae_deployment_ri_001(): void
{
    (new RI())->setStatus('port1544_blemmyae_deployment_ri_002', true);
}

/**
 * Import legacy authors.
 */
function port1544_blemmyae_deployment_ri_002(): void
{
    $editorialTypeMap = [
        'Opinion' => 'perspective',
        'News' => 'news',
        'Feature' => 'feature',
        'Video' => 'video',
        'Perspectives' => 'perspective',
    ];

    $stagingArticleTypes = @array_map(
        static fn(array $row) => [
            'stagingId' => (int)$row[0],
            'stagingArticleType' => $row[1],
        ],
        Utils::importCsv(__DIR__ . '/port1544/stagingArticleTypes.csv', ',')
    );
    $stagingArticleTypes = array_column($stagingArticleTypes, 'stagingArticleType', 'stagingId');

    global $wpdb;
    $row = array_filter(
        $wpdb->get_results("SELECT import_id, post_id, unique_key FROM wp_pmxi_posts"),
        fn ($item) => array_key_exists($item->unique_key, $stagingArticleTypes)
    );

    foreach ($row as $item) {
        $editorialTypeSlug = $editorialTypeMap[$stagingArticleTypes[$item->unique_key]];
        $editorialTypeId = get_term_by('slug', $editorialTypeSlug, 'editorial_type');

        Logger::log(
            'Updating ' .
            $item->post_id .
            ', setting editorial type to ' .
            $editorialTypeSlug .
            ', was ' .
            $stagingArticleTypes[$item->unique_key] .
            ' on staging',
            'info'
        );
        update_field(EditorialCT::GROUP_EDITORIAL_TAXONOMY__FIELD_TYPE, $editorialTypeId, $item->post_id);
        do_action('acf/save_post', $item->post_id);
    }

    wp_cache_flush();
}
