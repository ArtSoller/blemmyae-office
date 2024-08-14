<?php

declare(strict_types=1);

namespace Scm\WP_GraphQL;

use Exception;
use WP_Term;
use WPGraphQL\Model\Post;
use WPGraphQL\Model\Term;

/**
 * Class Redirects.
 *
 * Adds "redirects" GraphQL field.
 */
class Taxonomy extends AbstractExtension
{
    /**
     * @inerhitDoc
     * @throws Exception
     */
    protected function registerFields(): void
    {
        register_graphql_field('ContentNode', 'mainTopic', [
            'type' => 'TermNode',
            'description' => __('Main Topic', 'First Topic'),
            'resolve' => static function (Post $post, $args, $context, $info): ?Term {
                $topics = get_field('topic', $post->databaseId);
                return isset($topics[0]) ? ($topics[0] instanceof WP_Term ? new Term($topics[0]) : null) : null;
            },
        ]);

        $config = [
            'type' => ['list_of' => 'TermNode'],
            'description' => __('Taxonomy terms of a certain nesting level.'),
            'args' => [
                'taxonomy' => [
                    'type' => ['non_null' => 'TaxonomyEnum'],
                    'description' => __('Taxonomy name.'),
                ],
                'depth' => [
                    'type' => ['non_null' => 'Int'],
                    'description' => __(
                        // phpcs:ignore
                        'Level of nesting. 0 (or negative number) for top-leveled terms, 1 for children etc. If argument is bigger then actual depth, no terms will be returned.'
                    ),
                ],
            ],
            'resolve' => static fn(
                $source,
                $args,
                $context,
                $info
            ): array => self::taxonomyTermsByDepthResolver($args),
        ];
        register_graphql_field('RootQuery', 'taxonomyTermsByDepth', $config);
    }

    /**
     * Resolve callback for taxonomyTermsByDepth GraphQL endpoint.
     * Get the terms of a certain nesting level for some taxonomy.
     *
     * @param array $args
     *
     * @return array WPGraphQL\Model\Term objects, constructed from WP_Term.
     * @throws Exception On Term object construction error.
     */
    public static function taxonomyTermsByDepthResolver(array $args): array
    {
        $taxonomy = $args['taxonomy'] ?? '';
        $depth = $args['depth'];

        if (!empty($taxonomy)) {
            $hierarchy = \Scm\Tools\Utils::sortTermsHierarchically(
                get_terms(
                    $taxonomy,
                    ['hide_empty' => false]
                )
            );
            return array_map(
                static fn($term) => new Term($term) ?? null,
                Utils::getTermsFromHierarchyByDepth($hierarchy, $depth)
            );
        }

        return [];
    }

    /**
     * @inheritDoc
     */
    protected function registerTypes(): void
    {
        // TODO: Implement registerTypes() method.
    }
}
