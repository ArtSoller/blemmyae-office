<?php

/**
 * RelatedBlock class. Resolves posts for related section of editorials
 *
 * @author   Nikita Sokolskiy (n_sokolskiy@dotwrk.com)
 */

declare(strict_types=1);

namespace Cra\BlemmyaeBlocks\BlockImplementations\CtEditorial;

use Cra\BlemmyaeBlocks\Block\AbstractNonPaginatedBlock;
use Cra\BlemmyaeBlocks\Block\BlockQueue;
use Cra\BlemmyaeBlocks\NativeAdsManager;

/**
 * RelatedBlock class
 */
class RelatedBlock extends AbstractNonPaginatedBlock
{
    /**
     * Currently the only value is the editorial id itself.
     *
     * @var int[]
     */
    private array $exclude = [];

    /**
     * @inheritDoc
     */
    public function init(
        array $block,
        array $path = [],
        ?BlockQueue $queue = null,
        string $applicationSlug = ''
    ): void {
        $this->exclude = $block['exclude'];

        $this->options = $block['options'] ?? [];
        $this->nativeAdFrequency = (int)($block['native_ad_frequency'] ?? 4);

        $this->initialNumberOfItems =
            (int)($block['number_of_items'] ?? 3);

        $this->numberOfNatives = NativeAdsManager::numberOfNativesSinglePage(
            $this->initialNumberOfItems,
            $this->nativeAdFrequency
        );

        $this->nativeAdTopics = $block['native_ad_topics'] ?? [];
        $this->postType = $block['post_type'] ?? ['editorial'];

        $this->collection = [
            'collection' => $block['collection'],
        ];

        $this->resolvedPostIds = $this->postCollectionIds();

        $this->taxonomyQuery = $block['taxonomy_query']['query_array'] ? $block['taxonomy_query'] : [
            'relation' => 'AND',
            'query_array' => [
                [
                    'terms' => [
                        $block['mainCategory'] ?? '',
                    ],
                    'operator' => 'IN',
                ],
            ],
        ];
        $this->numberOfItems = $this->calculateNumberOfItemsToResolve();

        $this->applications = $block['applications'] ?? [];
    }

    /**
     * @inheritDoc
     */
    public function excludePostIds(): array
    {
        return $this->exclude;
    }
}
