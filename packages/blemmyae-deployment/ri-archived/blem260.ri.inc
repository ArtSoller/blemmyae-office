<?php

/**
 * @link: https://cra.myjetbrains.com/youtrack/issue/BLEM-260
 */

const BLEM260_TOPIC = 'topic';
const BLEM260_BRAND = 'brand';

use Scm\Tools\Logger;

/**
 * @param $term_slug
 * @param string $taxonomy
 * @param string $post_type
 * @return array
 */
function blem260_posts_by_term_slug($term_slug, $taxonomy = 'topic', $post_type = 'post'): array
{
    $term = get_term_by('slug', $term_slug, $taxonomy);
    $post_ids = get_posts(
        array(
            'posts_per_page' => -1,
            'post_type' => $post_type,
            'fields' => 'ids',
            'tax_query' => array(
                array(
                    'taxonomy' => $taxonomy,
                    'field' => 'term_id',
                    'terms' => $term->term_id,
                )
            )
        )
    );

    return $post_ids ?? [];
}

/**
 * @param $termSlug
 */
function blem260_topic_term_to_brand_term_by_slug($termSlug): void
{
    $postIdsList = [];
    $postTypes = array_unique(array_keys(get_option('cptui_post_types', [])));
    foreach ($postTypes as $postType) {
        $postIdsList[] = blem260_posts_by_term_slug($termSlug, BLEM260_TOPIC, $postType);
    }
    // Flatten array.
    $postIds = call_user_func_array('array_merge', $postIdsList);
    $term = get_term_by('slug', $termSlug, BLEM260_BRAND);
    foreach ($postIds as $postId) {
        wp_set_post_terms($postId, $termSlug, BLEM260_BRAND, true);
        $fieldBrand = get_field(BLEM260_BRAND, $postId) ?: [];
        $fieldBrand[] = $term;
        update_field(BLEM260_BRAND, array_column($fieldBrand, 'term_id'), $postId);
        Logger::log("$postId is being updated with brand `$termSlug`", 'success');
        if ($termOld = get_term_by('slug', $termSlug, 'topic')) {
            $fieldTopic = get_field(BLEM260_TOPIC, $postId) ?: [];
            if (($key = array_search($termOld, $fieldTopic)) !== false) {
                unset($fieldTopic[$key]);
            }
            update_field(BLEM260_TOPIC, array_column($fieldTopic, 'term_id'), $postId);
            Logger::log("For $postId topic `$termSlug` was removed", 'success');
        }
    }
}

/**
 * Map SC Awards | WIT post to brand.
 */
function blem260_blemmyae_deployment_ri_001(): void
{
    $topicSlugs = [
        'sc-awards',
        'women-in-it-security',
    ];

    foreach ($topicSlugs as $topicSlug) {
        blem260_topic_term_to_brand_term_by_slug($topicSlug);
        $term = get_term_by('slug', $topicSlug, BLEM260_BRAND);
        wp_delete_term($term->ID, BLEM260_TOPIC);
    }

    wp_cache_flush();
}
