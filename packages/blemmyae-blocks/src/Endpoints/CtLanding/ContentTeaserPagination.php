<?php

/**
 * ContentTeaserPagination class. Creates 2 endpoints for offset-based pagination
 *
 * @author   Nikita Sokolskiy (n_sokolskiy@dotwrk.com)
 */

declare(strict_types=1);

namespace Cra\BlemmyaeBlocks\Endpoints\CtLanding;

use Cra\BlemmyaeApplications\BlemmyaeApplications;
use Cra\BlemmyaeBlocks\Block\AbstractBlock;
use Cra\BlemmyaeBlocks\Block\AbstractFeedBlock;
use Cra\BlemmyaeBlocks\Block\BlockFactory;
use Cra\BlemmyaeBlocks\BlockImplementations\CtLanding\ContentTeaserBlock;
use Cra\BlemmyaeBlocks\Endpoints\AbstractEndpoint;
use Cra\BlemmyaeBlocks\NativeAdsManager;
use Cra\BlemmyaeBlocks\Utility;
use Cra\CtPeople\PeopleCT;
use Exception;
use Scm\Tools\WpCore;
use WP_Query;
use WPGraphQL\Data\Connection\PostObjectConnectionResolver;
use WPGraphQL\Model\Post;

/**
 * ContentTeaserPagination class
 */
class ContentTeaserPagination extends AbstractEndpoint
{
    /**
     * @inheritDoc
     */
    public static function endpointFeedConnectionTitle(): string
    {
        return "contentTeaserPostsConnection";
    }

    /**
     * @var array<string, string>
     */
    private static array $pageTypeEnum = [
        'author' => 'AUTHOR',
        'pageWithFeed' => 'PAGE_WITH_FEED',
    ];

    /**
     * Stores corresponding block. Needed for insertion of dfp natives
     *
     * @var AbstractBlock
     */
    private AbstractBlock $block;

    public bool $authorHasNextPage = true;

    /**
     * @var int[]
     */
    public array $authorResolvedPostIds = [];

    /**
     * @inheritDoc
     */
    public function __construct(BlockFactory $blockFactory)
    {
        $this->registerTypes();
        $this->blockFactory = $blockFactory;
    }

    /**
     * Registers hooks.
     *
     * @return void
     */
    protected function registerTypes(): void
    {
        add_action('graphql_register_types', [$this, 'contentTeaserPosts'], 10);
    }

    /**
     * Registers hooks.
     *
     * @return void
     */
    protected function registerFields(): void
    {
    }

    /**
     * @inheritDoc
     */
    public function injectDfpNativesTeaser(array $connection, array $path): array
    {
        if (!($this->block instanceof ContentTeaserBlock)) {
            return $connection;
        }

        [
            AbstractFeedBlock::$blockOptions['nativeAds'] => $hasDfpNativeAds,
        ] = $this->block->parsedBlockOptions();

        if (!$hasDfpNativeAds) {
            return $connection;
        }

        $nativesOffset = NativeAdsManager::nativesOffset($this->block->nativeSequence, $this->block->page);
        $numberOfNatives = NativeAdsManager::numberOfNatives($this->block->nativeSequence, $this->block->page);

        $connection['nodes'] = NativeAdsManager::injectDfpNatives(
            $this->block->nativeAdFrequency,
            $connection['nodes'],
            $numberOfNatives,
            $nativesOffset
        );

        return $connection;
    }

    /**
     * Resolves post ids for author
     *
     * @param array<string, mixed> $args
     *
     * @return array
     * @throws Exception
     */
    private function authorResolvePostIds(array $args): array
    {
        // If we do not have authorSlug => return empty result.
        if (empty($args['slug'])) {
            throw new Exception("Empty slug for person in authorResolvedPostIds");
        }

        // Default args.
        $defaultArgs = [
            'postsPerPage' => -1,
            'orderBy' => 'DESC',
            'postType' => ['any'],
            'page' => 1,
            'applications' => [],
        ];

        // Get query args from merged args array.
        [
            'postsPerPage' => $postsPerPage,
            'page' => $paged,
            'slug' => $personSlug,
            'orderBy' => $orderBy,
            'postType' => $postType,
            'applications' => $applications,
        ] = array_merge($defaultArgs, $args);

        $person = WpCore::getPostBySlug($personSlug, PeopleCT::POST_TYPE);
        // @phpstan-ignore-next-line
        $personId = $person->ID;

        if (in_array($orderBy, ['ASC', 'DESC'], true)) {
            $args = [
                'post_type' => $postType,
                'orderby' => 'publish_date',
                'order' => $orderBy,
                'paged' => $paged,
                'posts_per_page' => $postsPerPage,
                'fields' => 'ids',
                'post_status' => 'publish',
                // @todo create universal solution for app tax query in CerberusApplication for ContentTeaserPosts
                // and authors posts.
                'tax_query' => [
                    [
                        'field' => 'slug',
                        'taxonomy' => BlemmyaeApplications::TAXONOMY,
                        'terms' => $applications,
                        'operator' => 'IN',
                    ],
                ],
            ];

            if (in_array('learning', $postType)) {
                $args['meta_query'] = [
                    [
                        'key' => 'speakers_$_speaker',
                        'value' => $personId,
                        'compare' => 'LIKE',
                    ],
                ];
            } else {
                $cacheExpirationTime = 5 * WpCore::MINUTE_IN_SECONDS;
                $group = 'person_posts';
                $result = wp_cache_get($personId, $group);
                if (!$result) {
                    $result = get_field('post', $personId, false);

                    if (!$result) {
                        return [-1];
                    }

                    wp_cache_set($personId, $result, $group, $cacheExpirationTime);
                }

                $args['post__in'] = array_map('intval', $result);
            }

            $query = new WP_Query($args);
            $result = $query->posts;
            $this->authorHasNextPage = $paged * $postsPerPage < $query->found_posts;

            return $result === [] ? [-1] : $result;
        }

        return [];
    }

    /**
     * Returns connection
     *
     * @param array<int, mixed> $wpGraphQlConfig
     * @param string $pageType
     *
     * @return array<string, mixed>
     * @throws Exception
     */
    public function resolveContentTeaserPosts(array $wpGraphQlConfig, string $pageType): array
    {
        /** @noinspection PhpUnusedLocalVariableInspection */
        [$id, $args, $context, $info] = $wpGraphQlConfig;

        switch ($pageType) {
            case self::$pageTypeEnum['author']:
                $this->authorResolvedPostIds = $this->authorResolvePostIds($args);
                break;

            case self::$pageTypeEnum['pageWithFeed']:
                $defaults = array_fill_keys(
                    [
                        'exclude',
                        'postsPerPage',
                        'page',
                        'taxonomyQuery',
                        'options',
                        'blockName',
                        'postType',
                        'nativeAdFrequency',
                        'nativeAdTopics',
                        'nativeAdSponsor',
                        'applications',
                    ],
                    null
                );

                [
                    'exclude' => $exclude,
                    'postsPerPage' => $postsPerPage,
                    'page' => $page,
                    'taxonomyQuery' => $taxQuery,
                    'options' => $options,
                    'blockName' => $blockName,
                    'postType' => $postType,
                    'nativeAdFrequency' => $nativeAdFrequency,
                    'nativeAdTopics' => $nativeAdTopics,
                    'nativeAdSponsor' => $nativeAdSponsor,
                    'applications' => $applications,
                ] = $args + $defaults;

                $block = $this->blockFactory->createBlock($blockName, BlockFactory::$blockTypes['contentTeaser']);
                $this->block = $block;
                $this->block->init([
                    'name' => $blockName,
                    'options' => $options,
                    'native_ad_frequency' => $nativeAdFrequency,
                    'native_ad_topics' => $nativeAdTopics,
                    'post_type' => $postType,
                    'number_of_items' => $postsPerPage,
                    'taxonomy_query' => $taxQuery,
                    'page' => $page,
                    'exclude' => $exclude,
                    'native_ad_sponsor' => $nativeAdSponsor,
                    'applications' => $applications ?? [],
                ]);

                $this->block->resolvePostIds();
        }

        // wp-graphql resolve function expects to receive non-null value here.
        return [];
    }


    /**
     * Function registers contentTeaserPosts and contentTeaserPostsAuthor
     * graphQL fields
     *
     * @throws Exception
     */
    public function contentTeaserPosts(): void
    {
        // Register union type.
        $toType = 'ContentTeaserPosts';

        $paginationArgs = [
            'page' => [
                'type' => 'Integer',
                'description' => __('Page number', 'blemmyae-blocks'),
            ],
            'postsPerPage' => [
                'type' => 'Integer',
                'description' => __('Page size', 'blemmyae-blocks'),
            ],
            'slug' => [
                'type' => 'String',
                'description' => __('Author slug', 'blemmyae-blocks'),
            ],
            'orderBy' => [
                'type' => 'String',
                'description' => __('ASC or DESC', 'blemmyae-blocks'),
            ],
            'applications' => [
                'type' => ['list_of' => 'ID'],
                'description' => __('List of application slugs.', 'blemmyae-blocks'),
            ],
        ];

        // Register union type ContentTeaserPosts.
        register_graphql_union_type(
            $toType,
            [
                'typeNames' => $this->blockFactory->getBlocksConfig()['list_with_image']['supportedPostTypes'],
                'resolveType' => static function (Post $post): string {
                    return Utility::resolveUnionType($post);
                },
            ]
        );

        // Register Connection Interface and object for Content Teaser posts.
        Utility::registerConnectionInterfaceForSpecificType(
            'ContentTeaserPostsConnection',
            $toType
        );

        // Content Teaser post.
        register_graphql_connection([
            'fromType' => 'ContentTeaserPostsPagination',
            'toType' => $toType,
            'fromFieldName' => 'contentTeaserPostsConnection',
            'connectionTypeName' => 'ContentTeaserPostsConnection',
            'resolve' =>
                function ($id, $args, $context, $info) {
                    $resolver = new PostObjectConnectionResolver(
                        $id,
                        $args,
                        $context,
                        $info
                    );
                    $blockPosts = empty($this->block->resolvedPostIds)
                        ? [-1]
                        : $this->block->resolvedPostIds;
                    $resolver->set_query_arg('post__in', $blockPosts);
                    $resolver->set_query_arg('orderby', 'post__in');

                    return $resolver->get_connection();
                },
        ]);

        // Register Connection Interface and object for Author Content Teaser.
        Utility::registerConnectionInterfaceForSpecificType(
            'AuthorContentTeaserPostsConnection',
            $toType
        );

        // Author Content Teaser Post.
        register_graphql_connection([
            'fromType' => 'AuthorContentTeaserPostsPagination',
            'toType' => $toType,
            'fromFieldName' => 'authorContentTeaserPostsConnection',
            'connectionTypeName' => 'AuthorContentTeaserPostsConnection',
            'resolve' =>
                function ($id, $args, $context, $info) {
                    $resolver = new PostObjectConnectionResolver(
                        $id,
                        $args,
                        $context,
                        $info
                    );
                    $resolver->set_query_arg('post__in', $this->authorResolvedPostIds);
                    $resolver->set_query_arg('orderby', 'post__in');

                    return $resolver->get_connection();
                },
        ]);

        // Register Connection Interface and object for Author Content Teaser.
        Utility::registerConnectionInterfaceForSpecificType(
            'AuthorLearningsTeaserPostsConnection',
            $toType
        );

        // Author Content Teaser Post.
        register_graphql_connection([
            'fromType' => 'AuthorLearningsTeaserPostsPagination',
            'toType' => $toType,
            'fromFieldName' => 'authorLearningsTeaserPostsConnection',
            'connectionTypeName' => 'AuthorLearningsTeaserPostsConnection',
            'resolve' =>
                function ($id, $args, $context, $info) {
                    $resolver = new PostObjectConnectionResolver(
                        $id,
                        $args,
                        $context,
                        $info
                    );
                    $resolver->set_query_arg('post__in', $this->authorResolvedPostIds);
                    $resolver->set_query_arg('orderby', 'post__in');

                    return $resolver->get_connection();
                },
        ]);

        register_graphql_object_type('ContentTeaserPostsPagination', [
            'description' => __('Content teaser pagination endpoint for feed case', 'blemmyae-blocks'),
            'fields' => [
                'hasNextPage' => [
                    'type' => 'boolean',
                    'description' => __('Whether there is more content', 'blemmyae-blocks'),
                    'resolve' => fn() => $this->block->hasNextPage,
                ],
            ],
        ]);

        register_graphql_object_type('AuthorContentTeaserPostsPagination', [
            'description' => __('Content teaser pagination endpoint for author case', 'blemmyae-blocks'),
            'fields' => [
                'hasNextPage' => [
                    'type' => 'boolean',
                    'description' => __('Whether there is more content', 'blemmyae-blocks'),
                    'resolve' => fn() => $this->authorHasNextPage,
                ],
            ],
        ]);

        register_graphql_object_type('AuthorLearningsTeaserPostsPagination', [
            'description' => __('Learning teaser pagination endpoint for author case', 'blemmyae-blocks'),
            'fields' => [
                'hasNextPage' => [
                    'type' => 'boolean',
                    'description' => __('Whether there is more content', 'blemmyae-blocks'),
                    'resolve' => fn() => $this->authorHasNextPage,
                ],
            ],
        ]);

        register_graphql_field(
            'RootQuery',
            'contentTeaserPosts',
            [
                'type' => 'ContentTeaserPostsPagination',
                'args' => [
                    'exclude' => [
                        'type' => ['list_of' => 'Integer'],
                        'description' => __('List of post ids to exclude', 'blemmyae-blocks'),
                    ],
                    'page' => [
                        'type' => 'Integer',
                        'description' => __('Page number', 'blemmyae-blocks'),
                    ],
                    'postsPerPage' => [
                        'type' => 'Integer',
                        'description' => __('Page size', 'blemmyae-blocks'),
                    ],
                    'taxonomyQuery' => [
                        'type' => 'TaxQuery',
                        'description' =>
                            __('Taxonomy query object', 'blemmyae-blocks'),
                    ],
                    'options' => [
                        'type' => ['list_of' => 'String'],
                        'description' =>
                            __('Block options to determine what should be fetched', 'blemmyae-blocks'),
                    ],
                    'blockName' => [
                        'type' => 'String',
                        'description' =>
                            __('Block name to check support of specified options', 'blemmyae-blocks'),
                    ],
                    'postType' => [
                        'type' => ['list_of' => 'String'],
                        'description' => __('Post type', 'blemmyae-blocks'),
                    ],
                    'nativeAdFrequency' => [
                        'type' => 'Int',
                        'description' => __('Native ad frequency', 'blemmyae-blocks'),
                    ],
                    'nativeAdTopics' => [
                        'type' => ['list_of' => 'String'],
                        'description' =>
                            __(
                                'Topics that non-dfp natives should contain if nonDfpNatives option is selected',
                                'blemmyae-blocks'
                            ),
                    ],
                    'nativeAdSponsor' => [
                        'type' => 'Integer',
                        'description' =>
                            __('Native ad sponsor id', 'blemmyae-blocks'),
                    ],
                    'applications' => [
                        'type' => ['list_of' => 'ID'],
                        'description' => __('List of application slugs.', 'blemmyae-blocks'),
                    ],
                ],
                'resolve' =>
                    fn($id, $args, $context, $info) => $this->resolveContentTeaserPosts(
                        [$id, $args, $context, $info],
                        ContentTeaserPagination::$pageTypeEnum['pageWithFeed']
                    ),
            ]
        );

        register_graphql_field(
            'RootQuery',
            'authorContentTeaserPosts',
            [
                'type' => 'AuthorContentTeaserPostsPagination',
                'args' => [
                    ...$paginationArgs,
                    'postType' => [
                        'type' => ['list_of' => 'String'],
                        'description' => __('Post type', 'blemmyae-blocks'),
                    ],
                ],
                'resolve' =>
                    fn($id, $args, $context, $info) => $this->resolveContentTeaserPosts(
                        [$id, $args, $context, $info],
                        ContentTeaserPagination::$pageTypeEnum['author']
                    ),
            ]
        );

        register_graphql_field(
            'RootQuery',
            'authorLearningsTeaserPosts',
            [
                'type' => 'AuthorLearningsTeaserPostsPagination',
                'args' => $paginationArgs,
                'resolve' =>
                    function ($id, $args, $context, $info) {
                        $args['postType'] = ['learning'];

                        return $this->resolveContentTeaserPosts(
                            [$id, $args, $context, $info],
                            ContentTeaserPagination::$pageTypeEnum['author']
                        );
                    },
            ]
        );
    }
}
