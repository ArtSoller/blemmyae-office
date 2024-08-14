<?php

/** @noinspection PhpUnused */

/**
 * @author  Eugene Yakovenko <yakoveka@gmail.com>
 * @license proprietary
 * @link: https://cra.myjetbrains.com/youtrack/issue/PORT-3268
 */

declare(strict_types=1);


/**
 * Remove specific topics from sitemap.
 *
 * @return void
 */
function port3268_blemmyae_deployment_ri_002(): void
{
    // Term IDs for topics from the attached spreadsheet to
    // the https://cra.myjetbrains.com/youtrack/issue/PORT-3268/2024-06-02-SEO-check-incorrect-pages-in-sitemap.
    // For example, /topic/active-directory.
    $topicIds = [74647, 74620, 75098, 75129, 75127];

    foreach ($topicIds as $topicId) {
        update_field('field_6685708c5cd0f', ['remove'], 'topic_' . $topicId);
    }
}
