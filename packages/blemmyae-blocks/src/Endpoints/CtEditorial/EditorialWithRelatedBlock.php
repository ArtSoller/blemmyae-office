<?php

/**
 * LandingBySlug class, extends landing blocks types by adding resolvedPosts
 * field to each, features custom resolver for Landing type, along with methods
 * that pull data for resolvedPosts
 *
 * @author   Nikita Sokolskiy (n_sokolskiy@dotwrk.com)
 */

declare(strict_types=1);

namespace Cra\BlemmyaeBlocks\Endpoints\CtEditorial;

use Cra\BlemmyaeApplications\BlemmyaeApplications;
use Cra\BlemmyaeApplications\WP_GraphQL\Options;
use Cra\BlemmyaeBlocks\Block\AbstractFeedBlock;
use Cra\BlemmyaeBlocks\Block\BlockFactory;
use Cra\BlemmyaeBlocks\Endpoints\AbstractEndpoint;
use Cra\BlemmyaeBlocks\NativeAdsManager;
use Cra\BlemmyaeBlocks\Utility;
use Cra\CtEditorial\Editorial;
use Cra\CtEditorial\EditorialCT;
use Exception;
use GraphQL\Deferred;
use GraphQL\Type\Definition\ResolveInfo;
use Scm\Tools\WpCore;
use WP_Query;
use WPGraphQL\AppContext;
use WPGraphQL\Data\Connection\PostObjectConnectionResolver;
use WPGraphQL\Model\Post;

/**
 * LandingBySlug class.
 */
class EditorialWithRelatedBlock extends AbstractEndpoint
{
    protected string $postType = EditorialCT::POST_TYPE;

    public static function endpointFeedConnectionTitle(): string
    {
        return "resolvedPostCollection";
    }

    /**
     * Stores corresponding block. Needed for insertion of dfp natives
     *
     * @var ?AbstractFeedBlock
     */
    private ?AbstractFeedBlock $block = null;

    /**
     * Adds actions to register graphql types and fields
     */
    public function __construct(BlockFactory $blockFactory)
    {
        $this->registerTypes();
        $this->registerFields();
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
        if (!($this->block instanceof AbstractFeedBlock)) {
            return $connection;
        }

        [
            AbstractFeedBlock::$blockOptions['nativeAds'] => $hasDfpNativeAds,
        ] = $this->block->parsedBlockOptions();

        if (!$hasDfpNativeAds) {
            return $connection;
        }

        $numberOfNatives = NativeAdsManager::numberOfNativesSinglePage(
            $this->block->initialNumberOfItems,
            $this->block->nativeAdFrequency
        );

        $connection['nodes'] = NativeAdsManager::injectDfpNatives(
            $this->block->nativeAdFrequency,
            $connection['nodes'],
            $numberOfNatives,
        );

        return $connection;
    }

    /**
     * @return void
     */
    protected function registerTypes(): void
    {
        add_action('graphql_register_types', [$this, 'extendEditorialType'], 10);
    }

    /**
     * @return void
     */
    protected function registerFields(): void
    {
        add_action('graphql_register_types', [$this, 'editorialWithRelatedBlock'], 10);
    }

    /**
     * Extends collection widget block's block types to include field with resolved posts
     *
     * @throws Exception
     */
    public function extendEditorialType(): void
    {
        $editorialRelatedBlockConfig = $this->blockFactory->getBlocksConfig()['editorial_related_block'];

        $unionTypeName = 'Editorial_Editorialadvanced_RelatedBlock_ResolvedPostCollection';
        $connectionTypeName = 'Editorial_Editorialadvanced_RelatedBlock_ResolvedPostCollectionConnection';

        // Register union type.
        // @see https://www.wpgraphql.com/functions/register_graphql_union_type.
        register_graphql_union_type(
            $unionTypeName,
            [
                'typeNames' => $editorialRelatedBlockConfig['supportedPostTypes'],
                'resolveType' => static function (Post $post): string {
                    return Utility::resolveUnionType($post);
                },
            ]
        );

        // Register Connection Interface.
        Utility::registerConnectionInterfaceForSpecificType($connectionTypeName, $unionTypeName);

        // @see https://www.wpgraphql.com/functions/register_graphql_connection.
        register_graphql_connection(
            [
                'fromType' => 'Editorial_Editorialadvanced_RelatedBlock',
                'toType' => $unionTypeName,
                'fromFieldName' => 'resolvedPostCollection',
                'connectionTypeName' => $connectionTypeName,
                'resolve' =>
                    function (array $id, array $args, AppContext $context, ResolveInfo $info): Deferred {
                        $resolver = new PostObjectConnectionResolver($id, $args, $context, $info);
                        $blockPosts = empty($this->block->resolvedPostIds) ? [-1] : $this->block->resolvedPostIds;

                        $resolver->set_query_arg('post__in', $blockPosts);
                        $resolver->set_query_arg('orderby', 'post__in');

                        // Start of dirty logic - sort finalists by ascending name.
                        // @todo: Make adjustable from block settings.
                        $blockPostTypes =
                            $id[EditorialCT::GROUP_EDITORIAL_ADVANCED__FIELD_RELATED_BLOCK__SUBFIELD_POST_TYPE] ?? [];
                        if (is_array($blockPosts)) { // Override if provided as custom collection.
                            global $wpdb;
                            $ids = implode(', ', $blockPosts);
                            $blockPostTypes = $wpdb->get_row(
                                "SELECT post_type FROM wp_posts WHERE ID IN ($ids)",
                                WpCore::ARRAY_N
                            );
                        }
                        if (array_unique($blockPostTypes ?? []) === ['sc_award_nominee']) {
                            $resolver->set_query_arg('orderby', 'name');
                            $resolver->set_query_arg('order', 'ASC');
                        }

                        // End of dirty logic.
                        return $resolver->get_connection();
                    },
            ]
        );
    }

    /**
     * Function generates blocks queue and initiates queue resolving.
     *
     * @param array<string, mixed> $args
     * @param AppContext $context
     *
     * @return object|null
     * @throws Exception
     */
    protected function editorialWithRelatedBlockResolver(array $args, AppContext $context): ?object
    {
        $wpQuery = new WP_Query($args);

        if ($wpQuery->post_count !== 1) {
            return null;
        }

        $editorialPost = $wpQuery->posts[0];

        $editorialLoader = $context->get_loader('post')->load($editorialPost);

        $relatedBlockField = get_field(EditorialCT::GROUP_EDITORIAL_ADVANCED__FIELD_RELATED_BLOCK, $editorialPost);
        /** @phpstan-ignore-next-line */
        $mainTopic = Editorial::findMainTopic(
            get_field(
                EditorialCT::GROUP_EDITORIAL_TAXONOMY__FIELD_TOPIC,
                $editorialPost
            ) ?? []
        );

        /** @var AbstractFeedBlock $relatedBlock */
        $relatedBlock = $this->blockFactory->createBlock(
            'editorial_related_block',
            BlockFactory::$blockTypes['related']
        );

        $relatedBlock->init(
            [
                'options' => $relatedBlockField['options'],
                'native_ad_frequency' => $relatedBlockField['native_ad_frequency'],
                'post_type' => $relatedBlockField['post_type'],
                'number_of_items' => $relatedBlockField['number_of_items'],
                'taxonomy_query' => $relatedBlockField['taxonomy_query'],
                'exclude' => [$editorialPost],
                'mainCategory' => $mainTopic,
                'collection' => $relatedBlockField['collection'],
                'applications' => [BlemmyaeApplications::getAppIdByPostId($editorialPost)],
            ]
        );

        $relatedBlock->resolvePostIds();

        $this->block = $relatedBlock;

        return $editorialLoader;
    }

    /**
     * Registers landingBySLug graphQL field
     *
     * @throws Exception
     */
    public function editorialWithRelatedBlock(): void
    {
        $applicationsArgsConfig = [
            'applications' => [
                'type' => ['list_of' => 'ID'],
                'description' => __('List of application slugs.', 'ct-landing'),
            ],
        ];

        $config = [
            'type' => 'Editorial',
            'description' => __('Editorial data with resolved post references', 'administration'),
            'args' => [
                ...$applicationsArgsConfig,
                'slug' => [
                    'type' => 'ID',
                    'description' => __('Editorial SLUG only.', 'administration'),
                ],
            ],
            'resolve' =>
                fn($source, $args, $context, $info) => $this->editorialWithRelatedBlockBySlugResolver($args, $context),
        ];

        // Register new field.
        // @see https://www.wpgraphql.com/functions/register_graphql_field.
        register_graphql_field('RootQuery', 'editorialWithRelatedBlock', $config);
    }

    /**
     * Function generates blocks queue and initiates queue resolving.
     *
     * @param array<string, mixed> $args
     * @param AppContext $context
     *
     * @return object|null
     * @throws Exception
     */
    protected function editorialWithRelatedBlockBySlugResolver(array $args, AppContext $context): ?object
    {
        return $this->editorialWithRelatedBlockResolver(
            Options::graphqlEntityQueryArgs($args, $this->postType) ?? [],
            $context
        );
    }
}
