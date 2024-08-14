<?php

/**
 * LandingBySlug class, extends landing blocks types by adding resolvedPosts
 * field to each, features custom resolver for Landing type, along with methods
 * that pull data for resolvedPosts
 *
 * @author   Nikita Sokolskiy (n_sokolskiy@dotwrk.com)
 */

declare(strict_types=1);

namespace Cra\BlemmyaeBlocks\Endpoints\CtLanding;

use Cra\BlemmyaeBlocks\Block\AbstractFeedBlock;
use Cra\BlemmyaeBlocks\Block\AbstractTermBlock;
use Cra\BlemmyaeBlocks\Block\BlockFactory;
use Cra\BlemmyaeBlocks\Block\BlockQueue;
use Cra\BlemmyaeBlocks\NativeAdsManager;
use Cra\BlemmyaeBlocks\Utility;
use Exception;
use GraphQL\Deferred;
use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\Data\Connection\AbstractConnectionResolver;
use WPGraphQL\Data\Connection\PostObjectConnectionResolver;
use WPGraphQL\Data\Connection\TermObjectConnectionResolver;
use WPGraphQL\Registry\TypeRegistry;

/**
 * LandingBySlug class.
 */
class LandingByQueryResolver extends AbstractLandingByQueryResolver
{
    /**
     * @return string
     */
    public static function endpointFeedConnectionTitle(): string
    {
        return "resolvedPostCollection";
    }

    /**
     * @var string[]
     */
    private array $collectionWidgetBlockNames = [
        'simple_list',
        'list_with_image',
        'featured_post',
        'featured_feed_post',
        'featured_post_with_logo',
        'horizontal_list_with_image',
        'simple_list_with_image',
        'list_of_featured_events',
        'slideshow_of_events',
        'featured_list_of_whitepapers',
        'table_with_links',
    ];

    /**
     * Adds actions to register graphql types and fields
     */
    public function __construct(BlockFactory $blockFactory)
    {
        $this->registerTypes();
        $this->registerFields();
        $this->blockQueue = new BlockQueue();
        $this->blockFactory = $blockFactory;
    }

    /**
     * Injects dfp natives in wp graphql response
     *
     * @param array<string, mixed> $connection
     * @param string[] $path
     *
     * @return array<string, mixed>
     * @throws Exception
     */
    public function injectDfpNativesTeaser(array $connection, array $path): array
    {
        $block = $this->blockQueue->findBlockByPath($path);

        if (!($block instanceof AbstractFeedBlock)) {
            return $connection;
        }

        [
            AbstractFeedBlock::$blockOptions['nativeAds'] => $hasDfpNativeAds,
        ] = $block->parsedBlockOptions();

        if (!$hasDfpNativeAds) {
            return $connection;
        }

        $numberOfNatives = NativeAdsManager::numberOfNativesSinglePage(
            $block->initialNumberOfItems,
            $block->nativeAdFrequency
        );

        $connection['nodes'] = NativeAdsManager::injectDfpNatives(
            $block->nativeAdFrequency,
            $connection['nodes'],
            $numberOfNatives
        );

        return $connection;
    }

    /**
     * @return void
     */
    protected function registerTypes(): void
    {
        add_action('graphql_register_types', [$this, 'extendCollectionWidgetBlocksTypes'], 10);
        add_filter(
            'graphql_connection_amount_requested',
            [$this, 'graphqlConnectionAmountRequested'],
            11,  // Make sure it's run after other filters.
            2
        );
    }

    /**
     * @return void
     */
    protected function registerFields(): void
    {
        add_action('graphql_register_types', [$this, 'landingByQuery'], 10);
    }

    /**
     * Register DFP Native Ads Object Type.
     *
     * @return void
     */
    private function registerCerberusDfpNativeAdsObjectType(): void
    {
        register_graphql_object_type('CerberusDfpNativeAd', [
            'description' => 'CerberusDfpNative custom object type',
            'fields' => [
                'title' => [
                    'type' => 'String',
                ],
                'id' => [
                    'type' => ['non_null' => 'ID'],
                    'resolve' => fn() => bin2hex(random_bytes(20)),
                ],
                'databaseId' => [
                    'type' => ['non_null' => 'Int'],
                    'resolve' => fn(): int => random_int(10000000, 100000000),
                ],
            ],
        ]);
    }

    /**
     * Register Resolved post collection for blocks.
     *
     * @param array<string, mixed> $blockConfig
     *  Array with block configuration
     *
     * @return void
     * @throws Exception
     */
    private function registerBlocksResolvedPostCollection(array $blockConfig): void
    {
        $unionTypeName = $blockConfig['graphQLName'] . '_ResolvedPostCollection';
        $connectionTypeName = $unionTypeName . 'Connection';
        $isTermBlock = isset($blockConfig['type']) && $blockConfig['type'] === 'term';

        if (!$isTermBlock) {
            register_graphql_union_type(
                $unionTypeName,
                [
                    'typeNames' => $blockConfig['supportedPostTypes'],
                    'resolveType' => static function ($post): string {
                        return Utility::resolveUnionType($post);
                    },
                ]
            );
        }

        $toType = $isTermBlock ? 'TermNode' : $unionTypeName;

        // Register Connection Interface type.
        Utility::registerConnectionInterfaceForSpecificType($connectionTypeName, $toType);

        // Register connection.
        register_graphql_connection([
            'fromType' => $blockConfig['blockTypeName'],
            'toType' => $toType,
            'fromFieldName' =>
                $isTermBlock ? 'resolvedTermCollection' : 'resolvedPostCollection',
            'connectionTypeName' => $connectionTypeName,
            'resolve' =>
                function (array $id, array $args, AppContext $context, ResolveInfo $info): Deferred {
                    $blockPosts = [-1];
                    $block = $this->blockQueue->findBlockByPath($info->path);

                    if ($block && !empty($block->resolvedPostIds)) {
                        $blockPosts = $block->resolvedPostIds;
                    }

                    $isTermBlock = $block instanceof AbstractTermBlock;
                    $resolver = $isTermBlock ?
                        new TermObjectConnectionResolver($id, $args, $context, $info) :
                        new PostObjectConnectionResolver($id, $args, $context, $info);

                    if ($isTermBlock) {
                        $resolver->set_query_arg('include', $blockPosts);

                        return $resolver->get_connection();
                    }

                    $resolver->set_query_arg('posts_per_page', count($blockPosts));
                    $resolver->set_query_arg('post__in', $blockPosts);
                    $resolver->set_query_arg('orderby', 'post__in');

                    return $resolver->get_connection();
                },
        ]);
    }

    /**
     * Callback for filter 'graphql_connection_amount_requested'.
     *
     * @param int $amount
     * @param AbstractConnectionResolver $resolver
     *
     * @return int
     */
    public function graphqlConnectionAmountRequested(int $amount, AbstractConnectionResolver $resolver): int
    {
        $block = $this->blockQueue->findBlockByPath($resolver->getInfo()->path);
        if ($block && !empty($block->resolvedPostIds)) {
            // Set amount of posts returned by resolved post collection to the actual number of posts resolved.
            return count($block->resolvedPostIds);
        }
        return $amount;
    }

    /**
     * Extends collection widget block's block types to include field with resolved posts.
     *
     * Callback for action "graphql_register_types".
     *
     * @param TypeRegistry $registry
     *
     * @throws Exception
     * @noinspection PhpUnusedParameterInspection
     */
    public function extendCollectionWidgetBlocksTypes(TypeRegistry $registry): void
    {
        $this->registerCerberusDfpNativeAdsObjectType();

        foreach ($this->blockFactory->getBlocksConfig() as $blockName => $blockConfig) {
            if (!in_array($blockName, $this->collectionWidgetBlockNames, true)) {
                continue;
            }

            $this->registerBlocksResolvedPostCollection($blockConfig);
        }
    }

    /**
     * Registers LandingByQuery graphQL field.
     *
     * This function supports landingBySlug and landingPreviewById fields.
     *
     * @throws Exception
     */
    public function landingByQuery(): void
    {
        $applicationsArgsConfig = [
            'applications' => [
                'type' => ['list_of' => 'ID'],
                'description' => __('List of application slugs.', 'ct-landing'),
            ],
        ];

        $config = [
            'type' => 'Landing',
            'description' => __('Landing data with resolved post references', 'ct-landing'),
            'args' => [
                ...$applicationsArgsConfig,
                'slug' => [
                    'type' => 'ID',
                    'description' => __('Landing SLUG only.', 'ct-landing'),
                ],
            ],
            'resolve' =>
                fn($source, $args, $context, $info) => $this->landingBySlugResolver($args, $context),
        ];

        /**
         * @todo Discuss this logic and move it to landing field instead of create additional 2
         * endpoints with same logic.
         **/
        register_graphql_field('RootQuery', 'landingBySlug', $config);
        register_graphql_field('RootQuery', 'landingPreviewById', [
            'type' => 'Landing',
            'description' => __('Landing data with resolved post references', 'ct-landing'),
            'args' => [
                ...$applicationsArgsConfig,
                'id' => [
                    'type' => 'ID',
                    'description' => __('Landing ID only.', 'ct-landing'),
                ],
                'previewId' => [
                    'type' => 'ID',
                    'description' => __('Preview ID.', 'ct-landing'),
                ],
            ],
            'resolve' =>
                fn($source, $args, $context, $info) => $this->landingPreviewResolver($args, $context),
        ]);

        $config = [
            'type' => ['list_of' => 'Number'],
            'description' => __('Post ids of landing resolved posts', 'ct-landing'),
            'resolve' => fn($source, $args, $context, $info) => $this->blockQueue->resolvedPostIds,
        ];

        register_graphql_field('Landing', 'fetchedPostsList', $config);

        $listWithImageFetchedPostsListConfig = [
            'type' => ['list_of' => 'Number'],
            'description' => __(
                'Post ids of posts resolved by all blocks excluding the current one',
                'ct-landing'
            ),
            'resolve' => function (array $id, array $args, AppContext $context, ResolveInfo $info): array {
                $blockPosts = $this->blockQueue->findBlockByPath($info->path)->resolvedPostIds ?? [];

                return array_diff($this->blockQueue->resolvedPostIds, $blockPosts);
            },
        ];

        register_graphql_field(
            $this->blockFactory->getBlocksConfig()['list_with_image']['blockTypeName'],
            'fetchedPostsList',
            $listWithImageFetchedPostsListConfig
        );
    }
}
