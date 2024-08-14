<?php

declare(strict_types=1);

namespace Cra\BlemmyaeApplications\WP_GraphQL;

use Cra\BlemmyaeApplications\BlemmyaeApplications;
use Cra\BlemmyaeApplications\CerberusApps;
use Exception;
use WP_Query;
use WPGraphQL\AppContext;

/**
 * Class for custom landing resolvers.
 */
class Options
{
    /**
     * @param array<string, mixed> $args
     * @param AppContext $context
     * @param string $postType
     *
     * @return object|null
     * @throws Exception
     */
    public static function graphqlPostResolverByApplicationSlug(
        array $args,
        AppContext $context,
        string $postType
    ): ?object {
        if (!in_array($postType, BlemmyaeApplications::supportedPostTypes())) {
            return null;
        }
        // Load posts via wordpress query.
        $wpQuery = new WP_Query(self::graphqlEntityQueryArgs($args, $postType) ?? []);

        // If we have more than 1 post, we will not load any posts.
        if ($wpQuery->post_count !== 1) {
            return null;
        }

        // Get first landing post.
        $post = $wpQuery->posts[0];

        // Use landing loader.
        return $context->get_loader('post')->load($post);
    }

    /**
     * @param array<string, mixed> $args
     * @param string $postType
     * @return array<string, mixed>|null
     */
    public static function graphqlEntityQueryArgs(array $args, string $postType): ?array
    {
        if (empty($args['slug'])) {
            return null;
        }

        // Init query args.
        $wpQueryArgs = [];

        // Add application args to the query.
        // If we have application field, we will use application slug field instead of default slug.
        if (!empty($args['applications'])) {
            $applications = $args['applications'];

            // Convert all slugs into term id.
            $applications = is_array($applications) ? $applications : [$applications];

            $applications = array_map([
                'Cra\BlemmyaeApplications\Entity\Term',
                'getAppTermIdByAppSlug'
            ], $applications);

            $wpQueryArgs += [
                'meta_query' => [
                    'relation' => 'AND',
                    [
                        'key' => CerberusApps::APPLICATION_SLUG_FIELD_META_KEY,
                        'value' => $args['slug'],
                    ],
                ],
                'tax_query' => [
                    'relation' => 'AND',
                    [
                        'taxonomy' => BlemmyaeApplications::TAXONOMY,
                        'terms' => $applications,
                        'compare' => 'IN',
                    ],
                ],
            ];
        } else {
            $wpQueryArgs += [
                'name' => $args['slug'],
            ];
        }

        $wpQueryArgs += [
            'post_type' => $postType,
            'post_status' => \Scm\WP_GraphQL\Options::getPublicPostStatuses(),
            'fields' => 'ids',
        ];

        return $wpQueryArgs;
    }
}
