<?php

/**
 * PORT-1888.
 *
 * https://cra.myjetbrains.com/youtrack/issue/PORT-1888.
 */

declare(strict_types=1);

use Cra\CtEditorial\EditorialCT;
use Cra\CtLearning\LearningCT;
use ReleaseInstructions\Command\CoreCommand as RI;
use Scm\Tools\Logger;

const PORT1888_FIX_TERM_MAPPING_POSTS_TRANSIENT_NAME = 'port1888_taxonomy_fix_term_mapping_array';

/*
 * Disable RI for auto run.
 */
function port1888_administration_ri_001(): void
{
    Logger::log("Disable ri for migration and fixes. Please run all RIs manually", "info");

    // Disable update new migrated taxonomy RI.
    (new RI())->setStatus('port1888_administration_ri_002', true);
}

/**
 * Fix - map terms to the topics based on fields.
 */
function port1888_administration_ri_002(): void
{
    $logger = static function (string $msg, string $type = 'info'): void {
        $logMessagePrefix = "Fix: map terms to the topics based on fields";
        Logger::log("$logMessagePrefix - $msg", $type);
    };

    // Start log.
    $logger("Start");

    // Fix memory leak.
    wp_suspend_cache_addition(true);

    $terms = get_terms([
        'taxonomy' => TAXONOMY_TOPIC,
        'fields' => 'ids',
    ]);

    port1888_reassign_post_to_topic_terms($terms);

    $logger("Done", "success");

    wp_suspend_cache_addition(false);
}

/**
 * Assign terms to post based on the parent topic and topics fields.
 *
 * @param array $terms
 *  Array with term IDs.
 */
function port1888_reassign_post_to_topic_terms(array $terms): void
{
    wp_cache_flush();
    wp_suspend_cache_addition(true);

    $postsProcessed = get_transient(PORT1888_FIX_TERM_MAPPING_POSTS_TRANSIENT_NAME) ?? [];

    // Get all post types.
    $postTypes = get_post_types(['_builtin' => false]);

    // Go through post types, because it's easy to find error, based on post types.
    foreach ($postTypes as $postType) {
        // If we processed post before, we need to skip this update.
        /** @phpstan-ignore-next-line */
        $relatedPosts = getPostIdsRelatedToTerms(
            $terms,
            TAXONOMY_TOPIC,
            ['post_type' => $postType]
        );
        $relatedPosts = array_diff($relatedPosts, $postsProcessed);

        // Query for field.
        $getNonEmptyPostFields = function (int|string $postId, array $supportedFields): array {
            global $wpdb;

            // Prepare supported lists for `IN` in the SQL query -> add wrap every item
            // into '' and separate it by comma.
            $fields = array_map(fn($value): string => "'$value'", $supportedFields);
            $implodeValue = implode(", ", $fields);

            $query = sprintf(
                "SELECT meta_value FROM wp_postmeta WHERE post_id = %s AND meta_value IN (%s)",
                $postId,
                $implodeValue
            );

            return $wpdb->get_col($query) ?? [];
        };

        Logger::log("Previously processed posts was skipped.", "info");
        Logger::log(
            // phpcs:ignore
            "If you want to run this RI from scratch - remove transient value: " . PORT1888_FIX_TERM_MAPPING_POSTS_TRANSIENT_NAME,
            "info"
        );

        // We need to be sure that all assigned topics are correct.
        // Part of our content on the site has different value in some topic fields and assigned terms, as
        // result we need to fix that before migration. Fastest way - re-save field with same value.
        foreach ($relatedPosts as $postId) {
            // Init list of fields which we need to update.
            $termsFromFields = [];

            // Fix load terms issue.
            // In some posts we have terms assigned to post, but not added into the database. It works well in CMS,
            // because of `Load Terms` properties. We can fix this issue by re-saving whole post => data in DB will be
            // updated.
            if ($postType === 'learning') {
                // Load assigned value.
                $assignedTerms = wp_get_post_terms($postId, TAXONOMY_TOPIC, ['fields' => 'ids']);

                if (
                    empty($getNonEmptyPostFields(
                        $postId,
                        [LearningCT::GROUP_LEARNING_TAXONOMY__FIELD_TOPIC]
                    )) && !empty($assignedTerms)
                ) {
                    update_field(LearningCT::GROUP_LEARNING_TAXONOMY__FIELD_TOPIC, $assignedTerms, $postId);
                    continue;
                }
            }

            // In some cases we save text topic field with terms. Need to update this relation if we do not it in DB.
            if ($postType === 'editorial') {
                // If we have terms in text topic field => we need to re-save topic field in editorial.
                $textTopicField = 'field_60d5b0ce7d695';
                $textTopicValue = get_field($textTopicField, $postId, false);

                if (
                    is_array($textTopicValue) && empty($getNonEmptyPostFields(
                        $postId,
                        [EditorialCT::GROUP_EDITORIAL_TAXONOMY__FIELD_TOPIC]
                    ))
                ) {
                    Logger::log(
                        "Save topic text field ($textTopicField) value into topic field, post: $postId",
                        "info"
                    );
                    update_field(EditorialCT::GROUP_EDITORIAL_TAXONOMY__FIELD_TOPIC, $textTopicValue, $postId);
                }
            }

            // Get only fields related to topics.
            foreach (
                $getNonEmptyPostFields(
                    $postId,
                    port1888_fields_related_to_topics_taxonomy()
                ) as $field
            ) {
                // Get field value.
                $fieldObject = get_field_object($field, $postId, false);

                // Re-save it, if it's not empty => taxonomy will be assigned to the post automatically.
                if (!empty($fieldObject['value'])) {
                    // If save terms enabled => this term should be assigned to the post.
                    if ($fieldObject['save_terms']) {
                        // Convert value from string to int. We need to do that for future strict check of tids arrays.
                        $fieldValue = array_map(function ($item) {
                            return (int)$item;
                        }, $fieldObject['value']);

                        $termsFromFields = array_merge($termsFromFields, $fieldValue);
                    }
                }
            };

            // Remove duplicates.
            $termsFromFields = array_unique($termsFromFields);

            // Get assigned terms.
            $assignedTerms = wp_get_post_terms($postId, TAXONOMY_TOPIC, ['fields' => 'ids']);

            // Field should have similar sorting.
            sort($termsFromFields);
            sort($assignedTerms);

            // Re-save field with old value => topic will be assigned to post automatically.
            if ($termsFromFields !== $assignedTerms) {
                $postType = get_post_type($postId);

                // Log.
                Logger::log("Update terms for post $postId, post type: $postType", 'info');

                // To attach terms to post we need to save post or use wp_set_post_terms.
                // Set post terms is faster than save post => we will use it.
                wp_set_post_terms($postId, $termsFromFields, TAXONOMY_TOPIC);
            }

            // Save processed posts into transient.
            $postsProcessed[] = $postId;
            set_transient(PORT1888_FIX_TERM_MAPPING_POSTS_TRANSIENT_NAME, $postsProcessed);
        }
    }

    wp_suspend_cache_addition(false);
}

/**
 * List of fields related to topic.
 *
 * @return string[]
 */
function port1888_fields_related_to_topics_taxonomy(): array
{
    // @todo in innodata sync we have string topic field. Do we need to fix it or not in future iteration?
    // See field_60d5b0ce7d695.
    return [
        // inlineAdSettings.json
        'field_60ee661c839f8',
        // collectionWidget.json
        'field_60ee661c839f7',
        'field_60ee661c839f6',
        'field_63aadab43fdc3',
        'field_60ee661c839f5',
        'field_60ee661c839f4',
        'field_60ee661c839f2',
        'field_613f0935e20d2',
        'field_60ee661c839f1',
        'field_60ee661c839e9',
        // ppworks_episode_advanced.json
        'field_61af3b88ac984',
        // ppworks_segment_advanced.json
        'field_61af4677d96a7',
        // editorial_advanced.json.
        'field_625525855dee5',
        // editorial_advanced.json.
        // @todo they have tids in field settings. Need to update allow terms options after release.
        'field_6066d31112bc0',
        'field_6257b772835fd',
        // landing_taxonomy.json
        'field_607d0f183d443',
        // learning_taxonomy.json
        'field_607415300ff19',
        // generate_newsletter_options.json
        'field_6346306abcdb7',
        // newsletter_options.json
        'field_60af4d237f9e9',
        // people_taxonomy.json
        'field_607583ae4f5f5',
        // product_profile_taxonomy.json.
        'field_60753ba5b303b',
        // whitepaper_taxonomy.json
        'field_6075818b362e5',
    ];
}
