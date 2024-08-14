<?php

/**
 * BlockWpQueryManager class. Used with all AbstractBlock descendants
 *
 * @author   Nikita Sokolskiy (n_sokolskiy@dotwrk.com)
 */

declare(strict_types=1);

namespace Cra\BlemmyaeBlocks\WpQuery;

use Cra\BlemmyaeBlocks\Block\AbstractFeedBlock;

/**
 * BlockWpQueryManager class
 */
class BlockWpQueryManager extends AbstractWpQueryManager
{
    public function __construct(AbstractFeedBlock $block)
    {
        $this->numberOfItems = $block->numberOfItems;
        $this->exclude = $block->excludePostIds();
        $this->taxonomyQuery = $block->taxonomyQuery;
        $this->postType = $block->postType;

        [
            AbstractFeedBlock::$blockOptions['upcomingEvents'] => $upcomingEvents,
            AbstractFeedBlock::$blockOptions['onDemandEvents'] => $onDemandEvents,
            AbstractFeedBlock::$blockOptions['ongoingEvents'] => $ongoingEvents,
        ] = $block->parsedBlockOptions();

        $this->isLearningQuery = $upcomingEvents || $onDemandEvents || $ongoingEvents;
        $this->isUpcomingEvents = $upcomingEvents;
        $this->isOngoingEvents = $ongoingEvents;
        $this->numberOfNatives = $block->numberOfNatives;
        $this->pageOffset = $block->pageOffset;
        $this->nativeAdTopics = $block->nativeAdTopics;
        $this->nativeAdSponsor = $block->nativeAdSponsor;
        $this->nativePageOffset = $block->nativePageOffset;
        $this->applications = $block->applications;
        $this->author = $block->author;
    }
}
