<?php

/**
 * @link: https://cra.myjetbrains.com/youtrack/issue/PORT-1768
 */

declare(strict_types=1);

use ReleaseInstructions\Command\CoreCommand as RI;
use Scm\Tools\Logger;

/**
 * Set long-running RI as completed, so it can be manually triggered after deployment.
 *
 * @return void
 */
function port1768_ct_editorial_ri_003(): void
{
    (new RI())->setStatus('port1768_ct_editorial_ri_004', true);
}

/**
 * Check that related block has default settings, and it was not overwritten.
 *
 * @param $postId
 *  Post ID.
 *
 * @return bool
 */
function port1768_is_default_related_block($postId): bool
{
    $relatedBlockDefaultValues = [
        'related_block_title' => 'Related',
        'related_block_post_type' => null,
        'related_block_taxonomy_query_relation' => 'OR',
        'related_block_taxonomy_query_query_array' => null,
        'related_block_number_of_items' => '3',
        'related_block_collection' => null,
        'related_block_native_ad_frequency' => '4',
        'related_block_options' => null,
    ];

    // If related block contains smth non-default just retun false.
    foreach ($relatedBlockDefaultValues as $fieldName => $defaultValue) {
        $fieldValue = get_field($fieldName, $postId);
        if ($fieldValue !== $defaultValue) {
            return false;
        }
    }

    return true;
}
/**
 * Update settings for related content block.
 */
function port1768_ct_editorial_ri_004(): void
{
    // Increase execution time.
    set_time_limit(0);

    // Get list of all published editorial posts.
    // We do not need to work with natives and sw-article.
    $query = new WP_Query([
        'posts_per_page' => -1,
        'post_type' => 'editorial',
        'post_status' => 'publish',
        'tax_query' => [
            [
                'operator' => 'NOT IN',
                'taxonomy' => 'editorial_type',
                'field' => 'slug',
                'terms' => ['native'],
            ],
        ],
        'fields' => 'ids',
    ]);

    $count = 0;
    $total = count($query->get_posts());

    $port1768UpdatedPosts = [];

    foreach ($query->posts as $postId) {
        try {
            // Work with options and number of items settings for editorial.
            $optionsField = 'related_block_options';
            $numberOfItemsField = 'related_block_number_of_items';
            $nativeAdsValue = 'nativeAds';

            // Update related block option, if related block section was not updated before.
            if (port1768_is_default_related_block($postId)) {
                update_field($optionsField, [$nativeAdsValue], $postId);
                update_field($numberOfItemsField, 4, $postId);
                $port1768UpdatedPosts[] = $postId;
                Logger::log("Update related block section for post - $postId.", 'success');
            }

            Logger::log("Processed $count/$total editorials", 'info');
            $count++;
        } catch (Exception $exception) {
            Logger::log($exception->getMessage(), 'warning');
        }
    }

    Logger::log("List of updated posts: " . implode(", ", $port1768UpdatedPosts), 'info');
    Logger::log("Related posts section settings has been updated for " . count($port1768UpdatedPosts) . " editorials.", 'info');
}
