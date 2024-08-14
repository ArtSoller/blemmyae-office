<?php

/**
 * @link: https://cra.myjetbrains.com/youtrack/issue/BLEM-551
 * @noinspection PhpUnused
 */

use Cra\BlemmyaePpworks\PpworksEpisodeCT;
use Cra\BlemmyaePpworks\PpworksSegmentCT;
use Cra\WebhookConsumer\Mapper\Vendor\Ppworks\Episode;
use Cra\WebhookConsumer\Mapper\Vendor\Ppworks\Segment;
use Cra\WebhookConsumer\WebhookMapping;
use Cra\WebhookConsumer\WebhookMessageHandler;
use ReleaseInstructions\Command\CoreCommand as RI;
use Scm\Tools\Logger;

/**
 * Set status of the next long-running RI to completed because it should be called manually.
 */
function blem551_blemmyae_webhook_consumer_ri_001(): void
{
    (new RI())->setStatus('blem551_blemmyae_webhook_consumer_ri_002', true);
}

/**
 * Clean up bad ppworks data.
 */
function blem551_blemmyae_webhook_consumer_ri_002(): void
{
    $query = new WP_Query([
        'nopaging' => true,
        'post_type' => [
            PpworksEpisodeCT::POST_TYPE,
            PpworksSegmentCT::POST_TYPE,
        ],
        'post_status' => ['publish', 'unfinished'],
        'post__not_in' => _blem551_get_podcast_post_ids(),
    ]);
    $startTime = time();
    while ($query->have_posts()) {
        $post = $query->next_post();

        $unprocessedPostsCount = $query->post_count - $query->current_post;
        $completed = round(100 * $query->current_post / $query->post_count, 2);
        $elapsed_time = _blem551_format_elapsed_time($startTime, time());
        Logger::log(
            "Processed $completed% of posts in $elapsed_time. Left to process: $unprocessedPostsCount.",
            'info'
        );

        if (wp_delete_post($post->ID, true)) {
            Logger::log("Successfully deleted $post->post_title ($post->ID, $post->post_type).", 'success');
        } else {
            Logger::log("Error deleting $post->post_title ($post->ID, $post->post_type).", 'warning');
        }
    }
}

/**
 * Get post IDs of episodes and segments in wp_webhook_mappings.
 *
 * @return array<int>
 */
function _blem551_get_podcast_post_ids(): array
{
    $postIds = [];
    /** @var WebhookMapping[] $mappings */
    $mappings = WebhookMapping::findByVendorAndType(
        WebhookMessageHandler::VENDOR__PPWORKS,
        [Episode::TYPE, Segment::TYPE]
    );
    foreach ($mappings as $mapping) {
        $postIds[] = $mapping->postId;
    }
    return $postIds;
}

/**
 * Format elapsed time in easy to comprehend format.
 *
 * @param int $startTime
 * @param int $now
 *
 * @return string
 */
function _blem551_format_elapsed_time(int $startTime, int $now): string
{
    $seconds = $now - $startTime;
    if ($seconds < 60) {
        return "$seconds seconds";
    }
    if ($seconds < 3600) {
        $minutes = round($seconds / 60);
        return "$minutes minutes";
    }
    $hours = round($seconds / 3600);
    return "$hours hours";
}
