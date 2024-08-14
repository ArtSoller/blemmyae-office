<?php

/**
 * @link: https://cra.myjetbrains.com/youtrack/issue/BLEM-443
 */

const BLEM443_TOPIC_TAXONOMY = 'topic';

use Cra\CtEditorial\EditorialCT;
use Scm\Tools\Logger;

/**
 * Rename sub-category and assign to a new topic.
 */
function blem443_blemmyae_deployment_ri_003(): void
{
    $termNew = [
        'name' => 'Endpoint Security',
        'description' => 'Please, update description for Endpoint Security.',
        'slug' => 'endpoint-security',
    ];
    $msgPrefix = 'Term `' . $termNew['name'] . '` - ';

    $termOldParent = get_term_by('name', 'Architecture', BLEM443_TOPIC_TAXONOMY);
    if ($termOldParent instanceof \WP_Term) {
        if (term_exists('Endpoint security', BLEM443_TOPIC_TAXONOMY, $termOldParent->term_id)) {
            $termOld = get_term_by('name', 'Endpoint security', BLEM443_TOPIC_TAXONOMY);
            if ($termOld instanceof \WP_Term) {
                Logger::log('Term `Endpoint security` ~> `Device Security`. Updating', 'info');
                $update = wp_update_term($termOld->term_id, BLEM443_TOPIC_TAXONOMY, [
                    'name' => 'Device Security',
                    'slug' => 'device-security',
                ]);
                if (!is_wp_error($update)) {
                    Logger::log('Term `Endpoint security` ~> `Device Security`. Updated', 'success');
                }
            }

            if (term_exists('Device Security', BLEM443_TOPIC_TAXONOMY, $termOld->term_id)) {
                Logger::log($msgPrefix . 'already exists. Skipping', 'info');
                return;
            }
            Logger::log($msgPrefix . 'does not exist in db. Adding', 'info');
            $termCreated = wp_insert_term(
                $termNew['name'],
                BLEM443_TOPIC_TAXONOMY,
                [
                    'description' => $termNew['description'],
                    'slug' => $termNew['slug'],
                    'parent' => $termOld->term_id,
                ]
            );

            if (!is_wp_error($termCreated)) {
                Logger::log($msgPrefix . 'created', 'success');
            }
        }
        Logger::log('Term `Device Security` - already exists. Skipping', 'info');
    }

    $termNewParent = get_term_by('name', 'Device Security', BLEM443_TOPIC_TAXONOMY);
    if ($termNewParent instanceof \WP_Term) {
        // Get all editorial brieef posts in publish & draft states.
        $queryArg = [
            'post_type' => 'any',
            'numberposts' => -1,
            'fields' => 'ids',
            'tax_query' => [
                [
                    'taxonomy' => BLEM443_TOPIC_TAXONOMY,
                    'field' => 'name',
                    'terms' => 'Device Security',
                ],
            ],
        ];
        $publishPosts = get_posts($queryArg);

        // Exit if none found.
        if (!$publishPosts) {
            return;
        }

        $term = get_term_by('name', $termNew['name'], BLEM443_TOPIC_TAXONOMY);
        foreach ($publishPosts as $page) {
            // Append term to all.
            wp_set_post_terms($page, $term->term_id, BLEM443_TOPIC_TAXONOMY, true);
            // Save acf topics.
            $topicIds = get_field(EditorialCT::GROUP_EDITORIAL_TAXONOMY__FIELD_TOPIC, $page, false);
            $topicIds[] = $termNewParent->term_id;
            $topicIds[] = $term->term_id;
            update_field(EditorialCT::GROUP_EDITORIAL_TAXONOMY__FIELD_TOPIC, $topicIds, $page);
            Logger::log('post:id  - ' . $page . ' tagged with term: ' . $termNew['name'], 'info');
        }

        Logger::log('Term `Device Security` - already exists. Skipping', 'info');
    }
}
