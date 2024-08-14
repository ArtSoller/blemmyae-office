<?php

/**
 * @link https://cra.myjetbrains.com/youtrack/issue/PORT-2062
 *
 * @author  Konstantin Gusev <guvkon.net@icloud.com>
 * @license proprietary
 */

declare(strict_types=1);

use Cra\BlemmyaePpworks\PpworksEpisodeCT;
use Cra\BlemmyaePpworks\PpworksSegmentCT;
use Scm\Tools\Logger;

/**
 * Remove looping PPWorks redirects.
 *
 * @return void
 */
function port2062_blemmyae_webhook_consumer_ri_001(): void
{
    foreach (Red_Item::get_all() as $item) {
        if ($item->get_action_type() !== 'url' || !$item->is_enabled()) {
            continue;
        }

        $sourceUrl = $item->get_url();
        $targetUrl = $item->get_action_data();
        [$postType, $slug] = _post_type_and_slug_from_url($sourceUrl);
        if (!$postType) {
            // Not PPWorks - skipping.
            continue;
        }

        $query = new WP_Query(['post_type' => $postType, 'name' => $slug]);

        if (!$query->post_count) {
            // Redirect from a non-canonical URL - this is allowed.
            continue;
        }

        $post = $query->next_post();
        Logger::log(
            "Found bad podcast redirect: $sourceUrl => $targetUrl for post $post->ID",
            'info'
        );
        $item->delete();
    }
}

/**
 * @param string $url
 *
 * @return string[]|null[]
 */
function _post_type_and_slug_from_url(string $url): array
{
    $parts = explode('/', trim($url, '/'));
    switch ($parts[0]) {
        case 'podcast-episode':
            $postType = PpworksEpisodeCT::POST_TYPE;
            break;
        case 'podcast-segment':
            $postType = PpworksSegmentCT::POST_TYPE;
            break;
        default:
            return [null, null];
    }
    $slug = $parts[1];

    return [$postType, $slug];
}
