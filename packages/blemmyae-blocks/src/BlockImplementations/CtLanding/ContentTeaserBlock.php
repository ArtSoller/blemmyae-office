<?php

/**
 * ContentTeaserBlock class. Used in pagination resolvers
 *
 * @author   Nikita Sokolskiy (n_sokolskiy@dotwrk.com)
 */

declare(strict_types=1);

namespace Cra\BlemmyaeBlocks\BlockImplementations\CtLanding;

use Cra\BlemmyaeBlocks\Block\AbstractFeedBlock;
use Cra\BlemmyaeBlocks\Block\BlockQueue;
use Cra\BlemmyaeBlocks\NativeAdsManager;
use Cra\BlemmyaeBlocks\WpQuery\BlockWpQueryManager;
use Exception;

/**
 * ContentTeaserBlock class
 */
class ContentTeaserBlock extends AbstractFeedBlock
{
    /**
     * Since no global context is present for each pagination request,
     * array of post ids to exclude is a block field
     *
     * @var int[]
     */
    private array $exclude = [];

    /**
     * Stores native sequence - a sequence used to calculate correct number
     * of natives to inject on each page and offset for natives. Has the
     * following structure:
     * [
     *   [
     *     'numberOfPosts' => int
     *     'lastNativeOffset' => int
     *   ],
     *   ...,
     * ]
     * It has sequence_length <= native_ad_frequency(hypothesis, not proven strictly)
     * For page n we pick nativeSequence[n mod sequence_length]
     *
     * @var array<array{'numberOfPosts': int, 'lastNativeOffset': int}>
     */
    public array $nativeSequence = [];

    public bool $hasNextPage = false;

    /**
     * @inheritDoc
     */
    public function init(
        array $block,
        array $path = [],
        ?BlockQueue $queue = null,
        string $applicationSlug = ''
    ): void {
        $this->page = $block['page'] ?? 1;
        if (!$this->name) {
            throw new Exception("Block with empty title field");
        }
        $this->exclude = $block['exclude'] ?? [];

        $this->options = $block['options'] ?? [];
        $this->nativeAdFrequency = (int)($block['native_ad_frequency'] ?? 4);
        $this->nativeAdTopics = array_map(
            static fn(string $nativeAdTopic) => (object)['slug' => $nativeAdTopic],
            $block['native_ad_topics'] ?? []
        );
        $this->nativeAdSponsor = isset($block['native_ad_sponsor']) ? (object)[
            'ID' => $block['native_ad_sponsor'],
        ] : null;

        $this->postType = $block['post_type'] ?? [];

        $this->initialNumberOfItems =
            (int)($block['number_of_items'] ?? 10);

        $this->taxonomyQuery = $block['taxonomy_query'] ?? [];

        $this->nativeSequence = NativeAdsManager::constructSequence(
            $this->initialNumberOfItems,
            $this->nativeAdFrequency,
            $this->page
        );
        $this->numberOfNatives = NativeAdsManager::numberOfNatives($this->nativeSequence, $this->page);
        $this->numberOfItems = $this->calculateNumberOfItemsToResolve();
        $this->pageOffset = $this->calculatePageOffset();
        $this->nativePageOffset = $this->calculateNativePageOffset();
        $this->applications = $block['applications'] ?? [];
        $this->author = $block['author'] ?? null;
    }

    /**
     * Calculates offset for pagination - because of variable number of natives
     * for each page, page-based pagination is not going to work
     *
     * @return int
     */
    private function calculatePageOffset(): int
    {
        [
            self::$blockOptions['nonDfpNatives'] => $hasNonDfpNatives,
            self::$blockOptions['nativeAds'] => $hasNativeAds,
        ] = $this->parsedBlockOptions();

        if ($hasNonDfpNatives || $hasNativeAds) {
            $offset = 0;
            for ($i = 1; $i < $this->page; $i++) {
                $offset += $this->initialNumberOfItems - NativeAdsManager::numberOfNatives($this->nativeSequence, $i);
            }

            return $offset;
        }

        return ($this->page - 1) * $this->initialNumberOfItems;
    }

    /**
     * Calculates offset for pagination - needed to fetch next page of
     * natives feed
     *
     * @return int
     */
    private function calculateNativePageOffset(): int
    {
        [
            self::$blockOptions['nonDfpNatives'] => $hasNonDfpNatives,
        ] = $this->parsedBlockOptions();

        if ($hasNonDfpNatives) {
            $offset = 0;
            for ($i = 1; $i < $this->page; $i++) {
                $offset += NativeAdsManager::numberOfNatives($this->nativeSequence, $i);
            }

            return $offset;
        }

        return 0;
    }

    /**
     * @inheritDoc
     */
    public function resolvePostIds(): void
    {
        [
            self::$blockOptions['nonDfpNatives'] => $hasNonDfpNatives,
        ] = $this->parsedBlockOptions();

        if ($this->numberOfItems) {
            $nativesOffset = NativeAdsManager::nativesOffset($this->nativeSequence, $this->page);

            $wpQueryManager = new BlockWpQueryManager($this);
            $wpQuery = $wpQueryManager->wpQuery();
            $this->hasNextPage = ($this->pageOffset + $this->numberOfItems) < $wpQueryManager->totalPostCount;

            /** @var int[] $posts */
            $posts = $wpQuery->posts ?: [];
            $posts = [...$this->resolvedPostIds, ...$posts];

            if ($hasNonDfpNatives) {
                $posts = NativeAdsManager::injectNonDfpNatives(
                    $posts,
                    $this,
                    $nativesOffset,
                );
            }

            $this->resolvedPostIds = $posts;
        }
    }

    /**
     * @inheritDoc
     */
    public function excludePostIds(): array
    {
        return $this->exclude;
    }
}
