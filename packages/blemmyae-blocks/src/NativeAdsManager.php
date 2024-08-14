<?php

/**
 * NativeAdsManager class, used to inject natives in feeds
 *
 * @author   Nikita Sokolskiy (n_sokolskiy@dotwrk.com)
 */

declare(strict_types=1);

namespace Cra\BlemmyaeBlocks;

use Cra\BlemmyaeBlocks\Block\AbstractFeedBlock;
use Cra\BlemmyaeBlocks\Endpoints\AbstractEndpoint;
use Cra\BlemmyaeBlocks\WpQuery\BlockWpQueryManager;
use Exception;
use WP_Post;
use WPGraphQL\Model\Post;

/**
 * NativeAdsManager class
 */
class NativeAdsManager
{
    /**
     * List of RootQuery fields that should be processed
     *
     * @var array|string[]
     */
    public static array $nodesSupportingDfpNativesInjection = [
        'landingBySlug' => 'landingBySlug',
        'contentTeaserPosts' => 'contentTeaserPosts',
        'editorialWithRelatedBlock' => 'editorialWithRelatedBlock',
    ];

    /**
     * @param array<array{'numberOfPosts': int, 'lastNativeOffset': int}> $sequence
     * @param int $page
     *
     * @return int
     */
    public static function nativesOffset(array $sequence, int $page): int
    {
        if ($page < 2) {
            return 0;
        }
        $sequencePage = ($page - 2) % count($sequence);

        return $sequence[$sequencePage]['lastNativeOffset'];
    }

    /**
     * @param array<array{'numberOfPosts': int, 'lastNativeOffset': int}> $sequence
     * @param int $page
     *
     * @return int
     */
    public static function numberOfNatives(array $sequence, int $page): int
    {
        $sequencePage = ($page - 1) % count($sequence);

        return $sequence[$sequencePage]['numberOfPosts'];
    }

    /**
     * Constructs native sequence - a sequence used to calculate correct number
     * of natives to inject on each page and offset for natives. Has the
     * following structure:
     * [
     *   [
     *     'numberOfPosts' => int
     *     'lastNativeOffset' => int
     *   ],
     *   ...,
     * ]
     *
     * @param int $numberOfItems
     * @param int $nativeAdFrequency
     * @param int $page
     *
     * @return array<array{'numberOfPosts': int, 'lastNativeOffset': int}>
     */
    public static function constructSequence(int $numberOfItems, int $nativeAdFrequency, int $page): array
    {
        $numberOfPosts = (int)floor($numberOfItems / $nativeAdFrequency);
        $lastNativeOffset = $numberOfItems % $nativeAdFrequency;
        $resultSequence = [
            [
                'numberOfPosts' => $numberOfPosts,
                'lastNativeOffset' => $lastNativeOffset,
            ],
        ];
        $index = $page;
        while ($lastNativeOffset && $index) {
            $numberOfItemsWithOffset = ($numberOfItems + $lastNativeOffset);
            $numberOfPosts = (int)floor($numberOfItemsWithOffset / $nativeAdFrequency);
            $lastNativeOffset = $numberOfItemsWithOffset % $nativeAdFrequency;
            $resultSequence[] = [
                'numberOfPosts' => $numberOfPosts,
                'lastNativeOffset' => $lastNativeOffset,
            ];
            $index--;
        }

        return $resultSequence;
    }

    /**
     * Returns number of natives when only one first page is present - used to get
     * number of natives for landing feeds for landingBySlug
     *
     * @param int $numberOfItems
     * @param int $nativeAdFrequency
     *
     * @return int
     */
    public static function numberOfNativesSinglePage(int $numberOfItems, int $nativeAdFrequency): int
    {
        return (int)floor($numberOfItems / ($nativeAdFrequency ?: 3));
    }

    /**
     * Injects dummy wp graphql Post instances in $postList
     *
     * @param int $nativeAdFrequency
     * @param Post[] $postList
     * @param int $numberOfNatives
     * @param int $offset
     *
     * @return Post[]
     * @throws Exception
     */
    public static function injectDfpNatives(
        int $nativeAdFrequency,
        array $postList,
        int $numberOfNatives,
        int $offset = 0
    ): array {
        $postListWithNatives = [];
        $index = $offset;
        $dummyNativePost = new WP_Post(
            (object)[
                'ID' => 0,
                'post_type' => 'cerberus_dfp_native_ad',
            ]
        );
        $nonInjectedNativesCount = $numberOfNatives;
        foreach ($postList as $post) {
            if ($index && !($index % ($nativeAdFrequency - 1))) {
                $postListWithNatives[] = new Post($dummyNativePost);
                --$nonInjectedNativesCount;
            }
            $postListWithNatives[] = $post;
            ++$index;
        }
        if ($nonInjectedNativesCount) {
            $postListWithNatives[] = new Post($dummyNativePost);
        }

        return $postListWithNatives;
    }

    /**
     * Function attempts to fetch natives that satisfy block taxonomy query, if none are present,
     * returns natives that satisfy weak taxonomy query(i.e. natives of any category/editorial type)
     *
     * @param AbstractFeedBlock $block
     *
     * @return int[]
     */
    public static function fetchNonDfpNatives(AbstractFeedBlock $block): array
    {
        $wpQueryManager = new BlockWpQueryManager($block);
        $wpQueryManager->nativesModeEnable();
        $wpQueryNonDfpNatives = $wpQueryManager->wpQuery();

        // @phpstan-ignore-next-line We never trust WordPress.
        return $wpQueryNonDfpNatives->posts ?? [];
    }

    /**
     * Fetches needed number of natives, then injects their ids in block's
     * resolved post ids list
     *
     * @param int[] $postIdsList
     * @param AbstractFeedBlock $block
     * @param int $offset
     *
     * @return int[]
     */
    public static function injectNonDfpNatives(array $postIdsList, AbstractFeedBlock $block, int $offset = 0): array
    {
        $postIdsListWithNatives = [];
        $fetchedNatives = self::fetchNonDfpNatives($block);
        $nativeQueueIndex = 0;
        $i = $offset;
        foreach ($postIdsList as $postId) {
            if ($i && !($i % ($block->nativeAdFrequency - 1))) {
                $postIdsListWithNatives[] = $fetchedNatives[$nativeQueueIndex];
                ++$nativeQueueIndex;
            }
            $postIdsListWithNatives[] = $postId;
            ++$i;
        }

        if (!empty($fetchedNatives[$nativeQueueIndex])) {
            $postIdsListWithNatives[] = $fetchedNatives[$nativeQueueIndex];
        }

        return $postIdsListWithNatives;
    }

    /**
     * @param AbstractEndpoint[] $endpoints
     *
     * @throws Exception
     */
    public function __construct(array $endpoints)
    {
        $this->init($endpoints);
    }

    /**
     * Adds filter to allow injection of dfp natives in wp graphql response
     *
     * @param AbstractEndpoint[] $endpoints
     *
     * @return void
     * @throws Exception
     */
    private function init(array $endpoints): void
    {
        add_filter('graphql_connection', static function ($connection, $resolver) use ($endpoints) {
            $path = $resolver->getInfo()->path;
            $firstNodeName = $path[0];
            foreach ($endpoints as $endpointTitle => $endpoint) {
                if ($firstNodeName === $endpointTitle) {
                    if (end($path) !== $endpoint::endpointFeedConnectionTitle()) {
                        return $connection;
                    }

                    return $endpoint->injectDfpNativesTeaser($connection, $path);
                }
            }

            return $connection;
        }, 10, 7);
    }
}
