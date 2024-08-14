<?php

/**
 * @link: https://cra.myjetbrains.com/youtrack/issue/BLEM-224
 */

use Scm\Tools\Logger;

/**
 * Import entity configs.
 */
function blem224_blemmyae_deployment_ri_001(): void
{
    $processed = get_option('sw_libsyn', []);
    $pages = get_posts(
        [
            'post_type' => 'editorial',
            'numberposts' => -1,
            'exclude' => implode(",", $processed),
            'tax_query' => [
                [
                    'taxonomy' => 'editorial_type',
                    'field' => 'slug',
                    'terms' => 'group-podcast',
                    'include_children' => false,
                    'operator' => 'IN',
                ],
            ],
        ]
    );
    $countItems = $count = count($pages);
    $countSkippedItems = count($processed);
    Logger::log("Items skipped $countSkippedItems due to `sw_libsyn` option.", 'info');
    Logger::log("Items left to process $countItems:", 'info');
    foreach ($pages as $page) {
        $isUpdated = false;
        $slug = explode(' ', str_replace('#', '', $page->post_title));
        $code = strtolower($slug[0]);
        $swUrl = "https://securityweekly.com/$code$slug[1]";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $swUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);
        $dom = new DOMDocument();
        @$dom->loadHTML($output);
        $iframes = $dom->getElementsByTagName('iframe');
        foreach ($iframes as $item) {
            $src = $item->getAttribute('src');
            if (strpos($src, 'libsyn') !== false) {
                // phpcs:ignore
                $iframe = "<iframe src=\"$src\" class=\"tf_iframe_lazy\" style=\"border: none\" height=\"90\" width=\"100%\" scrolling=\"no\" allowfullscreen=\"\" webkitallowfullscreen=\"\" mozallowfullscreen=\"\" oallowfullscreen=\"\" msallowfullscreen=\"\"></iframe>";
                $page->post_content = $iframe;
                $processed[] = $page->ID;
                wp_update_post($page);
                update_option('sw_libsyn', $processed);
                $isUpdated = !$isUpdated;
                break;
            }
        }
        $count--;
        // phpcs:ignore
        $msg = "[$countItems:$count]Editorial#$page->ID: $page->post_title " . ($isUpdated ? "has been successfully updated with iframe from $swUrl" : "doesn't have iframe on $swUrl");
        Logger::log($msg, $isUpdated ? 'success' : 'info');
    }
    wp_cache_flush();
}
