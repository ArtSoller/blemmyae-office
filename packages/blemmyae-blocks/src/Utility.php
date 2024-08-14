<?php

/**
 * Utility class containing static data and functions
 *
 * @author   Nikita Sokolskiy (n_sokolskiy@dotwrk.com)
 */

declare(strict_types=1);

namespace Cra\BlemmyaeBlocks;

use Cra\BlemmyaePpworks\PpworksEpisodeCT;
use Cra\BlemmyaePpworks\PpworksSegmentCT;
use Cra\CtCompanyProfile\CompanyProfileCT;
use Cra\CtEditorial\EditorialCT;
use Cra\CtLanding\LandingCT;
use Cra\CtLearning\LearningCT;
use Cra\CtPeople\PeopleCT;
use Cra\CtPeople\TestimonialCT;
use Cra\CtProductProfile\ProductProfileCT;
use Cra\CtWhitepaper\WhitepaperCT;
use Exception;
use Scm\Tools\Logger;
use WPGraphQL\Model\Post;

/**
 * Utility class
 */
class Utility
{
    /**
     * @var array<string, string>
     */
    public static array $postTypesMap = [
        EditorialCT::POST_TYPE => 'Editorial',
        LearningCT::POST_TYPE => 'Learning',
        LandingCT::POST_TYPE => 'Landing',
        WhitepaperCT::POST_TYPE => 'Whitepaper',
        PeopleCT::POST_TYPE => 'Person',
        TestimonialCT::POST_TYPE => 'Testimonial',
        ProductProfileCT::POST_TYPE => 'ProductProfile',
        CompanyProfileCT::POST_TYPE => 'CompanyProfile',
        PpworksEpisodeCT::POST_TYPE => 'PpworksEpisode',
        PpworksSegmentCT::POST_TYPE => 'PpworksSegment',
        'cerberus_dfp_native_ad' => 'CerberusDfpNativeAd',
        'sc_award_nominee' => 'ScAwardNominee',
    ];

    /**
     * Blemmyae logger wrapper to add prefix with plugin name
     *
     * @param string $message
     *
     * @return void
     */
    public static function log(string $message): void
    {
        Logger::log('[blemmyae-blocks]' . $message, 'warning');
    }

    /**
     * Map WordPress post type names to GraphQL format, add
     * CerberusDfpNativeAd to list of supported post types
     * Example:
     * ["editorial", "product_profile"] =>
     * ["Editorial", "ProductProfile", "CerberusDfpNativeAd"]
     *
     * @param string[] $postTypes
     *
     * @return string[]
     */
    public static function postTypesToGraphQlFormat(array $postTypes): array
    {
        return [
            ...array_map(
                static fn($postType) => Utility::$postTypesMap[$postType],
                $postTypes
            ),
        ];
    }

    /**
     * Utility function to get first item in array by
     * subfield value
     *
     * @param array<string|int, mixed> $array
     * @param string $value
     *
     * @return array|null
     * @phpstan-ignore-next-line Cannot define shape of the resulting array.
     */
    public static function findArrayByFieldValue(array $array, string $value): ?array
    {
        foreach ($array as $subField) {
            if (isset($subField['name']) && $subField['name'] === $value) {
                return is_array($subField) ? $subField : null;
            }
        }
        return null;
    }

    /**
     * @param string $postType
     *
     * @return string
     */
    public static function graphQlTypeFromPostType(string $postType): string
    {
        return self::$postTypesMap[$postType] ?? '';
    }

    /**
     * Resolves union type based on returned post_type.
     * Exception is cerberus_dfp_native_ad - there is no way to get post type that
     * is not a registered post type from WpGraphQl/Model/Post, so null post_type
     * value is interpreted as a cerberus_dfp_native_ad post type
     *
     * @param Post $post
     *
     * @return string
     * @throws Exception
     */
    public static function resolveUnionType(Post $post): string
    {
        $postType = $post->post_type;
        if (!$postType) {
            return self::graphQlTypeFromPostType('cerberus_dfp_native_ad');
        }
        $graphQlType = self::graphQlTypeFromPostType($post->post_type);
        if (!$graphQlType) {
            // @todo: switch to specific exception, example: RuntimeException.
            throw new Exception('Specified type is not supported');
        }

        return $graphQlType;
    }

    /**
     * Prepare graphql path.
     *
     * Returns path excluding landingBySlug or landingPreviewById, collectionWidget, index in
     * collectionWidget from the start, and resolvedPosts from the end e.g.
     *
     * [
     *   'landingBySlug',
     *   'collectionWidget',
     *   'layouts',
     *   1,
     *   'columns',
     *   '1',
     *   'blocks',
     *   '1',
     *   'resolvedPostCollection'
     * ] => ['layouts', 1, 'columns', '1', 'blocks', '1']
     *
     * @param string[] $path
     *
     * @return string[]
     */
    public static function prepareWpGraphQlPath(array $path): array
    {
        // For preview path we need to remove 2 additional level -> preview, node.
        $offset = $path[1] === 'preview' ? 4 : 2;

        return array_slice($path, $offset, 6);
    }

    /**
     * Register Edge interface with node and cursor fields.
     *
     * @param string $interfaceName
     *  Name of the interface.
     * @param string $nodeType
     *  Types of the nodes.
     *
     * @return void
     * @throws Exception
     */
    public static function registerConnectionEdgeInterfaceForSpecificType(string $interfaceName, string $nodeType): void
    {
        $fields = [
            'node' => [
                'type' => $nodeType,
                'description' => __('The item at the end of the edge', 'wp-graphql'),
            ],
            'cursor' => [
                'type' => 'string',
                'description' => __('A cursor for use in pagination', 'wp-graphql'),
            ],
        ];

        // Register interface.
        register_graphql_interface_type($interfaceName, [
            'description' => sprintf(__("Connection between a Node and a connected %s", 'wp-graphql'), $nodeType),
            'fields' => $fields,
        ]);
    }

    /**
     * Register default connection interface.
     *
     * Default connection interface contains required fields: pageInfo, nodes, and edges.
     *
     * @param string $interfaceName
     *  Name of the interface.
     * @param string $nodeType
     *  Types of the nodes.
     *
     * @return void
     * @throws Exception
     */
    public static function registerConnectionInterfaceForSpecificType(string $interfaceName, string $nodeType): void
    {
        // Register default edge interface type.
        $edgeType = $interfaceName . 'Edge';
        self::registerConnectionEdgeInterfaceForSpecificType($edgeType, $nodeType);

        $fields = [
            'nodes' => [
                'type' => ['list_of' => $nodeType],
                'description' => __('The nodes of the connection, without the edges', 'wp-graphql'),
            ],
            'edges' => [
                'type' => ['list_of' => $edgeType],
                'description' => __('Edges for the connection', 'wp-graphql'),
            ],
            'pageInfo' => [
                'type' => 'WPPageInfo',
                'description' => __('Information about pagination in a connection', 'wp-graphql'),
            ],
        ];

        // Register default object type for connection.
        register_graphql_object_type($interfaceName, [
            'description' => sprintf(__("Connection between a Node and a connected %s", 'wp-graphql'), $nodeType),
            'fields' => $fields,
        ]);
    }
}
