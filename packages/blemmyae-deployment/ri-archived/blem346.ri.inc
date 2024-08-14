<?php

declare(strict_types=1);

const BLEM346_LAST_CHUNK_OPTION = 'blem346_last_chunk_value';

use ReleaseInstructions\Command\CoreCommand as RI;
use Scm\Tools\Logger;

function blem346_wpQueryArgs($page)
{
    // phpcs:ignore
    $notSupportedPostTypes = ['product-test', 'group-product-test', 'podcast', 'group-podcast', 'scrolling-show', 'video', 'brief'];

    return [
        'post_type' => 'editorial',
        'post_status' => 'publish',
        'posts_per_page' => 20,
        'paged' => $page,
        'tax_query' => [
            [
                'taxonomy' => 'editorial_type',
                'field' => 'slug',
                'operator' => 'NOT IN',
                'terms' => $notSupportedPostTypes,
            ]
        ],
    ];
}

function blem346_processChunk($wpQuery, &$noImagePostsCount, &$notParsedImagePostsCount)
{
    $regex = '/(https?:\/\/.+)-(\d+x\d+)\.(jpe?g|png)/m';

    while ($wpQuery->have_posts()) {
        $post = $wpQuery->next_post();
        $postId = $post->ID;

        $log = function (string $message, string $type) use ($postId): void {
            Logger::log($postId . ' - ' . $message, $type);
        };

        $featuredImageField = get_field('featured_image', $postId);
        $featuredImageCaptionField = get_field('featured_image_caption', $postId);
        $blocks = parse_blocks($post->post_content);
        $imageBlocksCount = 0;
        $imageBlockIndex = 0;
        $imageStyle = false;
        $imageXml = new DOMDocument();
        $img = '';
        $figcaption = '';
        foreach ($blocks as $index => $block) {
            if ($imageBlocksCount) {
                break;
            }
            if ($block['blockName'] == 'core/image') {
                if (array_key_exists('align', $block['attrs'])) {
                    $imageStyle = true;
                }

                $imageBlockIndex = $index;
                $htmlEntityDecoded = html_entity_decode($block['innerHTML']);
                $htmlEntityDecoded = preg_replace('/&/', '&amp;', $htmlEntityDecoded);
                $htmlEntityDecoded = preg_replace('/<br>/', '<br></br>', $htmlEntityDecoded);
                $htmlEntityDecoded = preg_replace('/<>/', '', $htmlEntityDecoded);
                $htmlEntityDecoded = preg_replace('/<meta .+>/mU', '', $htmlEntityDecoded);
                $htmlEntityDecoded = preg_replace('/\/sites\/2/m', '', $htmlEntityDecoded);
                // phpcs:ignore
                $htmlEntityDecoded = preg_replace('/<((?:https|http):\/\/creativecommons\.org\/licenses\/by[-\/.\d\w]+)>/mU', '<a rel="license" href="$1">$1</a>', $htmlEntityDecoded);

                if (!$imageXml->loadXML($htmlEntityDecoded)) {
                    $log('Invalid XML in block. Skipping', 'warning');
                    $notParsedImagePostsCount += 1;
                    continue;
                }
                $selector = new DOMXPath($imageXml);
                $img = $selector->query('//figure/img')->item(0);
                if (!$img) {
                    $log('No img tag found in block, attempting to change selector', 'notice');
                    $img = $selector->query('//figure/a/img')->item(0);
                    if (!$img) {
                        $log('No img tag found in block, skipping', 'notice');
                        continue;
                    }
                }

                $imageBlocksCount += 1;

                $img = $img->getAttribute('src');
                $figcaptionTag = $selector->query('//figure/figcaption')->item(0);
                $figcaptionInnerHTML = '';
                if (!is_object($figcaptionTag)) {
                    continue;
                }
                $figcaptionChildren = $figcaptionTag->childNodes;

                if ($figcaptionChildren) {
                    foreach ($figcaptionChildren as $child) {
                        $tmp_doc = new DOMDocument();
                        $tmp_doc->appendChild($tmp_doc->importNode($child, true));
                        $figcaptionInnerHTML .= $tmp_doc->saveHTML();
                    }
                    $figcaption = trim($figcaptionInnerHTML);
                }
            }
        }
        if (!$imageBlocksCount) {
            $log("No image found in content. Skipping", "info");
            update_field('show_featured_image', 0, $postId);
            continue;
        }

        $parsedImageName = [];
        $matches = preg_match($regex, $img, $parsedImageName);
        $imageUrl = $matches ? implode('.', [$parsedImageName[1], $parsedImageName[3]]) : $img;
        $imageAttachmentId = attachment_url_to_postid($imageUrl);

        if (!$imageAttachmentId) {
            $log("Image in content not found in media. Skipping", "notice");
            update_field('show_featured_image', 0, $postId);
            continue;
        }

        if ($imageStyle || !!$imageBlockIndex) {
            // phpcs:ignore
            $log("Image found in content is styled or is not first block in content. Setting \"Show featured image\" field to false.", "info");
            update_field('show_featured_image', 0, $postId);
        }

        if (!!$featuredImageField['id'] && ($featuredImageField['id'] !== $imageAttachmentId)) {
            // phpcs:ignore
            $log("Featured image exists and potential override found in content. Setting \"Show featured image\" field to false and skipping.", "info");
            update_field('show_featured_image', 0, $postId);

            continue;
        }

        if (!$featuredImageField['id']) {
            $log("Updating featured image field to $imageAttachmentId", "info");
            update_field('featured_image', $imageAttachmentId, $postId);
        }

        if ($figcaption && ($featuredImageCaptionField !== $figcaption)) {
            $log("Featured image caption field set to $figcaption", "info");
            update_field('featured_image_caption', $figcaption, $postId);
        } else {
            $log("Skipping featured image caption update. Nothing to update", "info");
        }

        if (!$imageStyle && !$imageBlockIndex) {
            unset($blocks[$imageBlockIndex]);
            $serializedBlocks = serialize_blocks($blocks);
            $updatedPostData = [
                'ID' => $post->ID,
                'post_content' => $serializedBlocks,
            ];
            wp_update_post($updatedPostData);
            $log("Removed image block from content", "info");
        }
    }
}

function blem346_blemmyae_deployment_ri_001(): void
{
    (new RI())->setStatus('blem346_blemmyae_deployment_ri_002', true);
}

function blem346_blemmyae_deployment_ri_002(): void
{
    set_time_limit(0);

    libxml_use_internal_errors(true);

    $noImagePostsCount = 0;
    $notParsedImagePostsCount = 0;

    $chunkNumber = get_option(BLEM346_LAST_CHUNK_OPTION, 1);

    while (true) {
        $wpQuery = new WP_Query(blem346_wpQueryArgs($chunkNumber));
        if (!$wpQuery->post_count) {
            break;
        }
        blem346_processChunk($wpQuery, $noImagePostsCount, $notParsedImagePostsCount);
        libxml_clear_errors();
        $chunkNumber += 1;
        update_option(BLEM346_LAST_CHUNK_OPTION, $chunkNumber);
    }

    Logger::log('Posts that do not have image block count - ' . $noImagePostsCount, 'notice');
    Logger::log('Not parsed image posts count - ' . $notParsedImagePostsCount, 'notice');

    delete_option(BLEM346_LAST_CHUNK_OPTION);
    libxml_use_internal_errors();
}
