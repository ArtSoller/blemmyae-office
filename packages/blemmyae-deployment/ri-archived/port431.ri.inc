<?php

/**
 * @link: https://cra.myjetbrains.com/youtrack/issue/PORT-431
 */

declare(strict_types=1);

use ReleaseInstructions\Command\CoreCommand as RI;
use Scm\Tools\Logger;
use Scm\Tools\Utils;

const PORT431_PROCESSED_POSTS_OPTION = 'port431_processed_posts_option';

function port431_blemmyae_deployment_ri_001(): void
{
    (new RI())->setStatus('port431_blemmyae_deployment_ri_002', true);
}

/**
 * Template created by gutenberg editor
 *
 * @param string $youtubeUrl
 *
 * @return array
 */
function port431_youtube_embed_template(string $youtubeUrl): array
{
    $youtubeUrl = 'https://www.youtube.com/watch?v=' . $youtubeUrl;
    $innerHtmlTemplate =
        // phpcs:ignore
        '<figure class="wp-block-embed is-type-video is-provider-youtube wp-block-embed-youtube wp-embed-aspect-16-9 wp-has-aspect-ratio"><div class="wp-block-embed__wrapper">
        ' . $youtubeUrl . '
        </div></figure>';

    return [
        'blockName' => 'core/embed',
        'attrs' =>
            [
                'url' => $youtubeUrl,
                'type' => 'video',
                'providerNameSlug' => 'youtube',
                'responsive' => true,
                'className' => 'wp-embed-aspect-16-9 wp-has-aspect-ratio',
            ],
        'innerBlocks' =>
            [
            ],
        'innerHTML' => $innerHtmlTemplate,
        'innerContent' => [$innerHtmlTemplate],
    ];
}

function port431_blemmyae_deployment_ri_002(): void
{
    $wpPostmeta = array_map(
        static fn(array $row) => [
            'meta_id' => $row[0],
            'staging_post_id' => $row[1],
            'meta_value' => $row[2],
        ],
        Utils::importCsv(__DIR__ . '/port431/wp_postmeta.csv')
    );

    global $wpdb;

    $processedPosts = get_option(PORT431_PROCESSED_POSTS_OPTION, []);

    foreach ($wpPostmeta as $metaEntry) {
        $stagingPostId = $metaEntry['staging_post_id'];
        // We need to find a post id of the post that corresponds to a staging post
        // phpcs:ignore
        $postId = $wpdb->get_row("SELECT import_id, post_id, unique_key FROM wp_pmxi_posts WHERE unique_key=$stagingPostId")->post_id ?? null;
        if (!$postId) {
            Logger::log('Could not find post id corresponding to staging post id, skipping' . $postId, 'info');
            continue;
        }
        if (in_array($stagingPostId, $processedPosts, true)) {
            Logger::log('Skipping post, because it already was updated, post id - ' . $postId, 'info');
            continue;
        };
        $postBlocks = parse_blocks(get_post((int) $postId)->post_content);
        foreach ($postBlocks as $postBlock) {
            if ($postBlock['blockName'] === 'core/embed') {
                Logger::log('Post contains embed video, skipping, post id - ' . $postId, 'info');
                // Continue outer foreach
                continue 2;
            }
        }
        $embeddedCode = unserialize($metaEntry['meta_value'])->Video->EmbeddedCode;
        // Empty array to store preg_match matches
        $embeddedCodeMatches = [];
        // All iframes with embedded videos have a structure https://www.youtube.com/embed/<VideoId>
        preg_match(
            "/(?:https?:\/{2})?(?:w{3}\.)?youtube.com\/embed\/([^\s&?]+)\"/m",
            $embeddedCode,
            $embeddedCodeMatches
        );
        $postBlocks = [...$postBlocks, port431_youtube_embed_template($embeddedCodeMatches[1])];
        $updatedPostData = [
            'ID' => $postId,
            'post_content' => serialize_blocks($postBlocks),
        ];
        $updateResult = wp_update_post($updatedPostData);
        if (!$updateResult) {
            Logger::log('Error occurred while updating post content, post id - ' . $postId, 'warn');
        }
        Logger::log('Embed video added to post content, post id - ' . $postId, 'info');
        $processedPostsOptionValue = get_option(PORT431_PROCESSED_POSTS_OPTION);
        // phpcs:ignore
        update_option(PORT431_PROCESSED_POSTS_OPTION, [...(is_countable($processedPostsOptionValue) ? $processedPostsOptionValue : []), $stagingPostId]);
    }

    wp_cache_flush();
}
