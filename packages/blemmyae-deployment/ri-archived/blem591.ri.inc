<?php

/**
 * @link: https://cra.myjetbrains.com/youtrack/issue/BLEM-591
 */

declare(strict_types=1);

use Cra\CtEditorial\EditorialCT;
use ReleaseInstructions\Command\CoreCommand as RI;
use Scm\Tools\Logger;

function blem591_blemmyae_deployment_ri_001(): void
{
    (new RI())->setStatus('blem591_blemmyae_deployment_ri_002', true);
}

function blem591_populate_term_array_with_parents(array $ids): array
{
    $parents = [];
    $terms = get_terms([
        'taxonomy' => 'topic',
        'include' => $ids,
        'orderby' => 'include',
        'hide_empty' => false
    ]);
    foreach ($terms as $term) {
        $parents = [
            ...$parents,
            ...array_map(
                static fn($value) => (string)$value,
                // Do not treat terms of second level as parents, because
                // terms of third level are not permitted in topic field
                array_filter(
                    get_ancestors($term->term_id, $term->taxonomy, 'taxonomy'),
                    static fn($termId) => !get_ancestors($termId, 'topic', 'taxonomy')
                )
            )
        ];
    }
    return array_unique($parents);
}

function blem591_get_content_topic_term(): ?WP_Term
{
    $contentTopicTermObject = get_term_by('slug', 'content', 'topic');
    if ($contentTopicTermObject) {
        return $contentTopicTermObject;
    }

    $uncategorizedTerm = get_term_by('slug', 'uncategorized', 'topic');
    $contentTopicTermData = wp_insert_term(
        'Content',
        'topic',
        ['parent' => $uncategorizedTerm->term_id]
    );
    if (!is_wp_error($contentTopicTermData)) {
        return get_term($contentTopicTermData['term_id']);
    }

    return null;
}

function blem591_blemmyae_deployment_ri_002(): void
{
    $editorialWpQuery = new WP_Query([
        'post_type' => 'editorial',
        'post_status' => 'publish',
        'fields' => 'ids',
        'posts_per_page' => -1
    ]);
    $editorialsWithEmptyTopicField = array_filter(
        array_map(
            static fn($postId) => [
                'id' => $postId,
                'topicField' => get_field('topic', $postId),
                'attachedTopicTerms' => wp_get_post_terms($postId, 'topic')
            ],
            $editorialWpQuery->posts
        ),
        static fn($editorialPost) => !$editorialPost['topicField']
    );

    $i = 1;
    $totalEditorialsWithEmptyTopicField = count($editorialsWithEmptyTopicField);
    $contentTopicTerm = blem591_get_content_topic_term();
    foreach ($editorialsWithEmptyTopicField as $editorialPost) {
        Logger::log($i++ . '/' . $totalEditorialsWithEmptyTopicField . ' ----------', 'info');
        if (!count($editorialPost['attachedTopicTerms'])) {
            Logger::log(
                $editorialPost['id'] . ' - post without terms - setting topic field to `content`',
                'info'
            );
            update_field(
                EditorialCT::GROUP_EDITORIAL_TAXONOMY__FIELD_TOPIC,
                [(string)$contentTopicTerm->term_id],
                $editorialPost['id']
            );

            continue;
        }
        $topicTermIds = array_map(
            static fn($term) => (string)$term->term_id,
            $editorialPost['attachedTopicTerms']
        );
        $topicParents = blem591_populate_term_array_with_parents($topicTermIds);
        // If in a list with topics there is a term and its predecessor, which is the term
        // of top level, this predecessor is moved to parent_topic field
        $topicsWithoutParents = array_values(array_diff($topicTermIds, $topicParents));

        Logger::log($editorialPost['id'] . ' - updating topic field', 'info');
        update_field(
            EditorialCT::GROUP_EDITORIAL_TAXONOMY__FIELD_TOPIC,
            $topicsWithoutParents,
            $editorialPost['id']
        );
        // Update of parent topic field is not triggered by post save,
        // updating it explicitly
        Logger::log($editorialPost['id'] . ' - updating parent topic field', 'info');
        update_field(
            EditorialCT::GROUP_EDITORIAL_TAXONOMY__FIELD_PARENT_TOPIC,
            $topicParents,
            $editorialPost['id']
        );
    }

    wp_cache_flush();
}
