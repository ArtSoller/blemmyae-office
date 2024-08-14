<?php

/**
 * @file DB Cleanup.
 *
 * @link https://cra.myjetbrains.com/youtrack/issue/BLEM-204
 */

declare(strict_types=1);

/**
 * Helper function for blem204_memory_usage().
 */
function _blem204_memory_usage(string $function): string
{
    $memUsage = $function(true);
    if ($memUsage < 1024) {
        return $memUsage . ' b';
    }
    if ($memUsage < 1048576) {
        return round($memUsage / 1024, 2) . ' kb';
    }
    return round($memUsage / 1048576, 2) . ' mb';
}

/**
 * Debug output of memory usage.
 */
function blem204_memory_usage()
{
    \WP_CLI::log('###########');
    \WP_CLI::log('Memory in use: ' . _blem204_memory_usage('memory_get_usage'));
    \WP_CLI::log('Peak usage: ' . _blem204_memory_usage('memory_get_peak_usage'));
    \WP_CLI::log('Memory limit: ' . ini_get('memory_limit'));
    \WP_CLI::log('###########');
}

/**
 * Removes orphaned terms.
 * @param array $options
 */
function blem204_remove_orphaned_terms(array $options): void
{
    $countAll = $count = count($options['items']);
    \WP_CLI::success("${options['type']} items to remove $countAll");
    foreach ($options['items'] as $item) {
        --$count;
        $msg = "[$countAll:$count] ${options['type']} with #$item";
        // Do not care about WP core errors. Since they are orphaned and it makes sense for them to produce errors.
        @wp_delete_term($item, $options['type'])
            ? \WP_CLI::success("$msg was removed.")
            : \WP_CLI::warning("$msg was skipped.");
    }
}

/**
 * Removes orphaned posts.
 * @param array $options
 */
function blem204_remove_orphaned_posts(array $options): void
{
    $countAll = $count = count($options['items']);
    \WP_CLI::success("${options['type']} items to remove $countAll");
    foreach ($options['items'] as $item) {
        --$count;
        $msg = "[$countAll:$count] ${options['type']} with #$item";
        // Do not care about WP core errors. Since they are orphaned and it makes sense for them to produce errors.
        @wp_delete_post($item, true)
            ? \WP_CLI::success("$msg was removed.")
            : \WP_CLI::warning("$msg was skipped.");
    }
}

/**
 * Loads orphaned terms.
 * @param string $taxonomy
 * @return array
 */
function blem204_load_orphaned_terms(string $taxonomy): array
{
    global $wpdb;
    $query = "SELECT DISTINCT t.term_id
         FROM wp_terms t
         INNER JOIN wp_term_taxonomy tax
         ON tax.term_id = t.term_id
         WHERE tax.taxonomy = '$taxonomy'";
    return array_column($wpdb->get_results($query, ARRAY_A), 'term_id');
}

/**
 * Loads orphaned posts.
 * @param string $postType
 * @return array
 */
function blem204_load_orphaned_posts(string $postType): array
{
    global $wpdb;
    $query = "SELECT DISTINCT p.ID
            FROM wp_posts p
            WHERE p.post_type = '$postType'";
    return array_column($wpdb->get_results($query, ARRAY_A), 'ID');
}

/**
 * Fast removes data by post type; skipping greedy hooks.
 * @param string $postType
 */
function blem204_fast_cleanup(string $postType): void
{
    global $wpdb;
    $query = "DELETE FROM wp_posts WHERE post_type='$postType'";
    $result = $wpdb->query($query);
    $result ? \WP_CLI::success("$result $postType content items were removed.")
        : \WP_CLI::warning("$postType content items were already removed.");
    $query = "DELETE FROM wp_postmeta WHERE post_id NOT IN (SELECT id FROM wp_posts)";
    $result = $wpdb->query($query);
    $result ? \WP_CLI::success("$result $postType postmeta items were removed.")
        : \WP_CLI::warning("$postType postmeta items were already removed.");
    $query = "DELETE FROM wp_term_relationships WHERE object_id NOT IN (SELECT id FROM wp_posts)";
    $result = $wpdb->query($query);
    $result ? \WP_CLI::success("$result $postType term relationship items were removed.")
        : \WP_CLI::warning("$postType term relationship  items were already removed.");
    wp_cache_flush();
}

/**
 * Drop orphaned legacy tables.
 */
function blem204_blemmyae_deployment_ri_001(): void
{
    global $wpdb;

    $tableName = $wpdb->prefix . 'vendor_service_actions';
    $wpdb->query($wpdb->prepare('DROP TABLE IF EXISTS `%s`', $tableName))
        ? \WP_CLI::success("$tableName was removed.")
        : \WP_CLI::warning("$tableName was already removed.");

    wp_cache_flush();
}

/**
 * Removes posts.
 */
function blem204_blemmyae_deployment_ri_002(): void
{
    // Increase execution time.
    set_time_limit(0);

    $orphanedPostTypeList = [
        'hm-html',
        'hm-group-test',
        'hm_newsletter',
        'hm-newsletter-issue',
        'hm-product-review',
        'hm-section-front',
        'hm-slideshow',
        'hm-webcast',
        'cra_whitepaper',
        'convertr_campaign',
        'custom_css',
        'customize_changeset',
        'ep-synonym',
        'oembed_cache',
        'newsml_post',
        #'redirect_rule',
        #'wp_block',
        'revision',
        'page',
        'post',
    ];

    foreach ($orphanedPostTypeList as $orphanedPostType) {
        blem204_memory_usage();
        blem204_fast_cleanup($orphanedPostType);
    }

    foreach ($orphanedPostTypeList as $orphanedPostType) {
        blem204_memory_usage();
        $posts = get_posts(
            [
                'post_type' => $orphanedPostType,
                'numberposts' => -1,
                'fields' => 'ids',
            ]
        );
        if (is_array($posts)) {
            blem204_remove_orphaned_posts(['type' => $orphanedPostType, 'items' => $posts]);
            wp_cache_flush();
        }
        $orphanedPosts = blem204_load_orphaned_posts($orphanedPostType);
        if (is_array($orphanedPosts)) {
            try {
                blem204_remove_orphaned_posts(
                    [
                        'type' => $orphanedPostType,
                        'items' => $orphanedPosts,
                    ]
                );
            } catch (Exception $exception) {
                // May produce internal warnings in a core. Do nothing.
            }
            wp_cache_flush();
        }
    }

    global $wpdb;

    // DELETE * FROM wp_postmeta WHERE post_id NOT IN (SELECT ID FROM wp_posts)
    $wpdb->query(
        'DELETE wp_postmeta FROM wp_postmeta
        LEFT JOIN wp_posts ON wp_posts.ID = wp_postmeta.post_id
        WHERE wp_posts.ID IS NULL'
    )
        ? \WP_CLI::success("orphaned post meta was removed.")
        : \WP_CLI::warning("orphaned post meta was already removed.");
    wp_cache_flush();
}

/**
 * Remove terms.
 */
function blem204_blemmyae_deployment_ri_003(): void
{
    // Increase execution time.
    set_time_limit(0);

    $orphanedTaxonomyList = [
        'cra_whitepaper_category',
        'cra_whitepaper_tag',
        'hm-article-type',
        'hm-negative-keyword',
        'hm-newsletter',
        'hm-print-issue',
        'hm-print-section',
        'hm-sponsor',
        'hm-webcast-topic',
        'hm-webcast-type',
        'newsml_mediatopic',
        'post_tag',
        'category',
    ];
    foreach ($orphanedTaxonomyList as $orphanedTaxonomy) {
        blem204_memory_usage();
        $terms = get_terms(
            [
                'taxonomy' => $orphanedTaxonomy,
                'fields' => 'ids',
                'hide_empty' => false,
            ]
        );
        if (is_array($terms)) {
            blem204_remove_orphaned_terms(['type' => $orphanedTaxonomy, 'items' => $terms]);
            wp_cache_flush();
        }
        $orphanedTerms = blem204_load_orphaned_terms($orphanedTaxonomy);
        if (is_array($orphanedTerms)) {
            try {
                blem204_remove_orphaned_terms(
                    [
                        'type' => $orphanedTaxonomy,
                        'items' => $orphanedTerms,
                    ]
                );
            } catch (Exception $exception) {
                // May produce internal warnings in a core. Do nothing.
            }
            wp_cache_flush();
        }
    }

    global $wpdb;

    // DELETE FROM wp_termmeta WHERE term_id NOT IN (SELECT term_id FROM wp_terms)
    $wpdb->query(
        'DELETE wp_termmeta FROM wp_termmeta
        LEFT JOIN wp_terms ON wp_terms.term_id = wp_termmeta.term_id
        WHERE wp_terms.term_id IS NULL'
    )
        ? \WP_CLI::success("orphaned term meta was removed.")
        : \WP_CLI::warning("orphaned term meta was already removed.");

    wp_cache_flush();
}
