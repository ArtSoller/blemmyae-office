<?php

/**
 * PORT-1815.
 *
 * https://cra.myjetbrains.com/youtrack/issue/PORT-1815.
 */

declare(strict_types=1);

use Cra\CtEditorial\EditorialCT;
use ReleaseInstructions\Command\CoreCommand as RI;
use Scm\Tools\Logger;
use Scm\Tools\Utils;

const PORT1815_TAXONOMY_MAPPING_ARRAY_TRANSIENT_NAME = 'port1815_taxonomy_mapping_array';
const PORT1815_TAXONOMY_MAPPING_ARRAY_REDIRECTS = 'port1815_taxonomy_mapping_redirects';
const PORT1815_PROCESSED_FIELDS_TRANSIENT = 'port1815_processed_fields';
const PORT1815_NEW_TERM_NAME_PREFIX = '(New Term)';

/**
 * Fix - map terms to the topics based on fields.
 */
function port1815_administration_ri_001(): void
{
    Logger::log("Disable ri for migration and fixes. Please run all RIs manually", "info");

    // Disable update new migrated taxonomy RI.
    (new RI())->setStatus('port1815_administration_ri_002', true);
    (new RI())->setStatus('port1815_administration_ri_003', true);
    (new RI())->setStatus('port1815_administration_ri_004', true);
    (new RI())->setStatus('port1815_administration_ri_005', true);
    (new RI())->setStatus('port1815_administration_ri_006', true);
    (new RI())->setStatus('port1815_administration_ri_007', true);

    // Set clear transient.
    set_transient(PORT1888_FIX_TERM_MAPPING_POSTS_TRANSIENT_NAME, []);
}

/**
 * Fix taxonomy mapping table - 2nd iteration.
 */
function port1815_administration_ri_002(): void
{
    // Rerun 1888 with field fixes.
    port1888_administration_ri_002();
}

/**
 * Build migration table for topic taxonomy.
 */
function port1815_administration_ri_003(): void
{
    // Load new and old taxonomy from csv. Number of columns should be the same.
    $mappingInnerStructure = [
        'current' => [],
        'new' => [],
    ];

    // Init arrays.
    $fileInfo = $taxonomyMapping = $mappingInnerStructure;

    $csvNamePrefix = 'port1815_taxonomy';

    // At this iteration we have two files with old and new taxonomy. Number of rows in this file should be the same.
    // If we have different number of rows => we forgot to map smth;
    // @todo maybe start to work only with one file instead of 2.
    foreach ($taxonomyMapping as $key => $item) {
        // Build file info array.
        $fileName = sprintf("%s_%s.csv", $csvNamePrefix, $key);
        $fileInfo[$key] = [
            'path' => __DIR__ . "/port1815/" . $fileName,
            'filename' => $fileName,
        ];

        // Move data from file into array.
        /** @phpstan-ignore-next-line */
        $taxonomyMapping[$key] = exportTopicFromCsvToArray($fileInfo[$key]['path']);
    }

    // Return error, if number of items is different in two CSV files.
    if (count($taxonomyMapping['new']) !== count($taxonomyMapping['current'])) {
        Logger::log(
        // phpcs:ignore
            'Number of current taxonomy terms is not equal to new. Mapping should work only with equal number of terms!',
            'error'
        );
    }

    // Add new taxonomy with prefix at the name and slug.
    // Prefix will be removed, when we will finish all RIs from this file.
    array_walk_recursive($taxonomyMapping['new'], function (&$item) {
        if (is_string($item)) {
            $item = implode(" ", [PORT1815_NEW_TERM_NAME_PREFIX, $item]);
        }
    });

    // Push new taxonomy into DB.
    /** @phpstan-ignore-next-line */
    insertTaxonomyFromArray($taxonomyMapping['new']);

    // Build map array.
    port1815_create_mapping_table($taxonomyMapping['current'], $taxonomyMapping['new']);
}

/**
 * Update terms in all fields.
 */
function port1815_administration_ri_004(): void
{
    Logger::log("Update terms in fields...", "info");
    Logger::log(Utils::memUsage(), 'debug');

    // Fix memory leak.
    wp_suspend_cache_addition(true);

    // Get mapping table.
    $mapArray = get_transient(PORT1815_TAXONOMY_MAPPING_ARRAY_TRANSIENT_NAME);

    // Fields, which use taxonomy topics.
    $fields = port1815_get_not_processed_taxonomy_related_fields();

    // Find all posts with non-empty fields.
    global $wpdb;

    // Array with terms, which shouldn't be migrated.
    $taxonomyNameSkipArray = ['Uncategorized', 'Registration'];
    $skipTermIds = [];
    foreach ($taxonomyNameSkipArray as $termName) {
        $term = get_term_by('name', $termName, TAXONOMY_TOPIC);

        // Put category.
        $skipTermIds[] = $term->term_id;

        $child = get_term_children($term->term_id, TAXONOMY_TOPIC);

        $skipTermIds = array_merge($skipTermIds, $child);
    }

    $processedFields = get_transient(PORT1815_PROCESSED_FIELDS_TRANSIENT) ?? [];

    // Remove parent topic field from fields array.
    $fields = array_filter(
        $fields,
        fn($field) => $field !== EditorialCT::GROUP_EDITORIAL_TAXONOMY__FIELD_PARENT_TOPIC
    );

    foreach ($fields as $field) {
        // Find post_d from post_meta and do not work with revisions,
        // because revision may contain old non-existing terms.
        $result = $wpdb->get_results(
            "SELECT post_id FROM wp_postmeta as wpm
            LEFT JOIN wp_posts wp ON wp.ID = wpm.post_id
            WHERE wpm.meta_value = '$field' AND wp.post_type != 'revision'"
        );

        if (empty($result)) {
            Logger::log("No posts with $field", "warning");
            continue;
        }

        // Load field for posts.
        $ids = array_column($result, 'post_id');

        foreach ($ids as $postId) {
            $fieldValue = get_field($field, $postId, false);

            // Replace field value.
            if (!empty($fieldValue)) {
                // Multiple fields.
                if (is_array($fieldValue)) {
                    $updatedValue = array_map(function ($termId) use (
                        $field,
                        $postId,
                        $mapArray,
                        $skipTermIds
                    ) {
                        // Do not work with already processed fields.
                        // Do not work with new terms in fields. All slugs for new terms start from `new-term-`.
                        $slug = get_term_field('slug', $termId);
                        if (in_array($termId, $mapArray) || str_starts_with($slug, 'new-term-')) {
                            Logger::log(
                                "Field $field for post $postId was processed before",
                                "info"
                            );
                            return $termId;
                        }

                        // Not isset and not processed.
                        if (
                            !isset($mapArray[$termId]) && !in_array(
                                $termId,
                                $mapArray
                            ) && !in_array($termId, $skipTermIds)
                        ) {
                            Logger::log(
                                "We do not have mapping for term: $termId, field: $field, post: $postId",
                                "error"
                            );
                        }

                        return $mapArray[$termId] ?? $termId;
                    }, $fieldValue);
                } else {
                    if (in_array($fieldValue, $mapArray)) {
                        Logger::log("Mo migration rule. Field: $field, post $postId", "info");
                        continue;
                    }

                    // Single field.
                    if (!isset($mapArray[$fieldValue]) && !in_array($fieldValue, $skipTermIds)) {
                        Logger::log("We do not have mapping for term: $fieldValue", "error");
                    }

                    $updatedValue = $mapArray[$fieldValue];
                }

                // Update field, if it has different value.
                if ($fieldValue !== $updatedValue) {
                    // Update field.
                    update_field($field, $updatedValue, $postId);

                    // Load post type for additional fixes.
                    $postType = get_post_type($postId);

                    // Editorial fix.
                    // Editorials have 2 topic fields => we need to assign posts from topic and parent topic fields.
                    if ($postType === 'editorial' && $field === EditorialCT::GROUP_EDITORIAL_TAXONOMY__FIELD_TOPIC) {
                        // Load data from parent topic field too and assign terms.
                        $parentTopics = get_field(
                            EditorialCT::GROUP_EDITORIAL_TAXONOMY__FIELD_PARENT_TOPIC,
                            $postId,
                            false
                        );

                        // Set all updated post terms.
                        wp_set_post_terms(
                            $postId,
                            [...$parentTopics, ...$updatedValue],
                            TAXONOMY_TOPIC
                        );
                    }

                    Logger::log(Utils::memUsage(), 'debug');
                    Logger::log("Field $field was updated for post $postId ($postType)", "info");
                } else {
                    Logger::log("No updates for field $field for post $postId", "info");
                }
            };
        }

        $processedFields[] = $field;
        set_transient(PORT1815_PROCESSED_FIELDS_TRANSIENT, $processedFields);
    }

    wp_suspend_cache_addition(false);

    Logger::log("Terms have been updated successfully!", "success");
}

/**
 * Remove old taxonomies and update slugs for new one.
 */
function port1815_administration_ri_005(): void
{
    wp_cache_flush();

    Logger::log("Remove old terms...", "info");

    // Remove old taxonomy.
    $mapArray = get_transient(PORT1815_TAXONOMY_MAPPING_ARRAY_TRANSIENT_NAME);
    $terms = array_keys($mapArray);

    foreach ($terms as $term) {
        // Check that we do not have assigned content.
        /** @phpstan-ignore-next-line */
        $posts = getPostIdsRelatedToTerms([$term]);

        if (!empty($posts)) {
            $msg = sprintf(
                "We still have tagged content for old term! Term: %s. Number of content: %s",
                $term,
                count($posts)
            );
            Logger::log($msg, "error");
        }

        wp_delete_term($term, TAXONOMY_TOPIC);
        Logger::log("Old term with id $term was removed", "info");
    }

    Logger::log("Old terms was removed!", "success");
}

/**
 * Remove prefix from title and slugs for new, imported terms.
 *
 * @return void
 */
function port1815_administration_ri_006(): void
{
    // Load terms with prefix.
    $query = new \WP_Term_Query([
        'taxonomy' => TAXONOMY_TOPIC,
        'hide_empty' => false,
        'fields' => 'ids',
        'name__like' => PORT1815_NEW_TERM_NAME_PREFIX,
    ]);

    $termsWithPrefix = $query->terms;

    Logger::log("Remove prefix from migrated terms and update their slugs...", "info");

    foreach ($termsWithPrefix as $termId) {
        $termObject = get_term($termId);

        // Remove prefix from slug.
        $slugPrefix = sanitize_title(PORT1815_NEW_TERM_NAME_PREFIX);

        // Remove prefix and update term.
        $update = wp_update_term($termObject->term_id, TAXONOMY_TOPIC, [
            'slug' => ltrim(str_replace($slugPrefix, '', $termObject->slug)),
            'name' => ltrim(str_replace(PORT1815_NEW_TERM_NAME_PREFIX, '', $termObject->name), '-'),
            'description' => ltrim(str_replace(
                PORT1815_NEW_TERM_NAME_PREFIX,
                '',
                $termObject->description
            )),
        ]);

        if (is_wp_error($update)) {
            Logger::log($update->get_error_message(), "error");
        }

        Logger::log("Term $termId was updated!", "success");
    }

    Logger::log("New terms was updated!", "success");

    wp_cache_flush();
}

/**
 * Import missing taxonomy items.
 * @return void
 */
function port1815_administration_ri_007(): void
{
    /** @phpstan-ignore-next-line */
    $topicTaxonomy = exportTopicFromCsvToArray(__DIR__ . '/port1815/topic-2023-07-13.csv');
    /** @phpstan-ignore-next-line */
    insertTaxonomyFromArray($topicTaxonomy);
    /** @phpstan-ignore-next-line */
    taxonomyPrint(TAXONOMY_TOPIC, true);
    wp_cache_flush();
    // Run init hook again so taxonomies imported above get registered.
    @do_action('init');
}

/**
 * List of non-processed fields related to topic taxonomy.
 *
 * @param bool $cleanProcessedFields
 *  Clean processed fields queue, if you want to start field migration from scratch.
 *
 * @return string[]
 */
function port1815_get_not_processed_taxonomy_related_fields(bool $cleanProcessedFields = false): array
{
    if ($cleanProcessedFields) {
        set_transient(PORT1815_PROCESSED_FIELDS_TRANSIENT, []);
    }

    // Get processed fields.
    $processedFields = get_transient(PORT1815_PROCESSED_FIELDS_TRANSIENT) ?: [];

    // Get all fields.
    $fields = port1888_fields_related_to_topics_taxonomy();

    return array_diff($fields, $processedFields);
}

/**
 * Array of old and new tids.
 *
 * Array structure should be:
 *
 * [
 * ...
 * 'old_tid' => 'new_tid',
 * ...
 * ]
 *
 * @param $currentNames
 *  List of term names for current (old) terms.
 * @param $newNames
 *  List of term names for new terms.
 */
function port1815_create_mapping_table(array $currentNames, array $newNames): void
{
    $mapArray = [];
    $redirectMap = [];

    $logMsgPrefix = "Build mapping array for slugs and IDs";
    Logger::log("$logMsgPrefix - Start", "info");

    // Start from 1, because we removed 1st string with title.
    foreach ($currentNames as $index => $currentName) {
        // Get terms.
        /** @phpstan-ignore-next-line */
        $newTermName = getTermNameFromHierarchicalArray($newNames[$index]);
        /** @phpstan-ignore-next-line */
        $oldTermName = getTermNameFromHierarchicalArray($currentName);

        // In this case we assume that we have the same index for new and current name arrays.
        $newTerm = get_term_by('name', $newTermName, TAXONOMY_TOPIC);
        $oldTerm = get_term_by('name', $oldTermName, TAXONOMY_TOPIC);

        // Log error, if some term does not exist.
        if (!$oldTerm || !$newTerm) {
            $msg = sprintf("%s term was not found!", !$oldTerm ? 'Old' : 'New');
            Logger::log($msg, "error");
        }

        // Put it into map array.
        $mapArray[$oldTerm->term_id] = $newTerm->term_id;
        $redirectMap[$oldTerm->slug] = $newTerm->slug;
    }

    // If we have migration from one similar elements to the same => we do not have migrations here.
    foreach ([$mapArray, $redirectMap] as &$migrationArray) {
        $migrationArray = array_filter($migrationArray, function ($item, $key) {
            return $item !== $key;
        }, ARRAY_FILTER_USE_BOTH);
    }

    set_transient(PORT1815_TAXONOMY_MAPPING_ARRAY_TRANSIENT_NAME, $mapArray);
    set_transient(PORT1815_TAXONOMY_MAPPING_ARRAY_REDIRECTS, $redirectMap);

    Logger::log("$logMsgPrefix - Done", "success");
}
