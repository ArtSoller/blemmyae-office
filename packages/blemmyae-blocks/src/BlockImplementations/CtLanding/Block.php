<?php

/**
 * Block class. Used with collection widget blocks
 *
 * @author   Nikita Sokolskiy (n_sokolskiy@dotwrk.com)
 */

declare(strict_types=1);

namespace Cra\BlemmyaeBlocks\BlockImplementations\CtLanding;

use Cra\BlemmyaeBlocks\Block\AbstractNonPaginatedBlock;
use Cra\BlemmyaeBlocks\Block\BlockQueue;
use Cra\BlemmyaeBlocks\NativeAdsManager;
use Exception;
use ValueError;

/**
 * Block class
 */
class Block extends AbstractNonPaginatedBlock
{
    public string|int $weight;

    /**
     * @var string[]
     */
    public array $path;

    public BlockQueue $context;

    /**
     * @inheritDoc
     */
    public function init(
        array $block,
        array $path = [],
        ?BlockQueue $queue = null,
        string $applicationSlug = ''
    ): void {
        if (!$queue) {
            throw new ValueError("Queue is required");
        }
        $this->context = $queue;
        if (!$this->name) {
            throw new Exception("Block with empty acf_fc_layout field");
        }
        $this->path = $path;
        $this->options = $block['options'] ?? [];
        $this->nativeAdFrequency = (int)($block['native_ad_frequency'] ?? 4);
        $this->postType = $block['post_type'] ?? $this->config['postType'] ?? [];

        $this->initialNumberOfItems =
            (int)($block['number_of_items'] ?? $this->config['numberOfItems'] ?? 10);

        $this->collection = [
            'collection' => $block['collection'] ?? [],
            'post' => $block['post'] ?? null,
        ];

        $this->author = $block['author'] ?? null;

        $this->resolvedPostIds = $this->postCollectionIds();

        $this->numberOfNatives = NativeAdsManager::numberOfNativesSinglePage(
            $this->initialNumberOfItems,
            $this->nativeAdFrequency
        );

        $this->nativeAdTopics = $block['native_ad_topics'] ?? [];
        $this->nativeAdSponsor = $block['native_ad_sponsor'] ?? null;

        $this->numberOfItems = $this->calculateNumberOfItemsToResolve();
        $this->taxonomyQuery =
            $block['taxonomy_query']['query_array'] ? $block['taxonomy_query'] : [];
        $this->weight = $block['block_weight'] ?? 0;

        $this->applications = [$applicationSlug];
    }

    /**
     * @inheritDoc
     */
    public function excludePostIds(): array
    {
        return $this->context->resolvedPostIds;
    }
}
