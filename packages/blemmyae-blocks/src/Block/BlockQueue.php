<?php

/**
 * BlockQueue class. Used to manage list of collection widget Block instances
 *
 * @author   Nikita Sokolskiy (n_sokolskiy@dotwrk.com)
 */

declare(strict_types=1);

namespace Cra\BlemmyaeBlocks\Block;

use Cra\BlemmyaeBlocks\Utility;

/**
 * BlockQueue class
 */
class BlockQueue
{
    /**
     * Stores instances of Block class
     *
     * @var array<int, mixed>
     */
    public array $blocks = [];

    /**
     * Stores processed post ids
     *
     * @var int[]
     */
    public array $resolvedPostIds = [];

    /**
     * @param int[] $postIds
     *
     * @return void
     */
    public function updateResolvedPostIds(array $postIds): void
    {
        $this->resolvedPostIds = [...$this->resolvedPostIds, ...$postIds];
    }

    /**
     * Used to find block depending on graphql path of the block
     *
     * @param string[] $path
     *
     * @return AbstractBlock|null
     */
    public function findBlockByPath(array $path): AbstractBlock|null
    {
        foreach ($this->blocks as $block) {
            $wpGraphQlPath = Utility::prepareWpGraphQlPath($path);
            if ($block->path === $wpGraphQlPath) {
                return $block;
            }
        }

        return null;
    }

    /**
     * @param AbstractBlock $block
     *
     * @return void
     */
    public function addBlock(AbstractBlock $block): void
    {
        $this->blocks[] = $block;
    }

    /**
     * Sorts array of blocks by weight field of the block. If block weight
     * is not set, it defaults to 0 in landingBySlug resolver
     *
     * @return array<int, mixed>
     */
    protected function sortQueue(): array
    {
        $queueToOrder = $this->blocks;
        usort(
            $queueToOrder,
            static fn($block1, $block2) => $block2->weight <=> $block1->weight
        );

        return $queueToOrder;
    }

    /**
     * Used to initiate resolving of posts when queue is formed and
     * update resolvedPostIds
     *
     * @return void
     */
    public function resolveQueue(): void
    {
        $this->blocks = $this->sortQueue();

        foreach ($this->blocks as $block) {
            $block->resolvePostIds();
            $this->updateResolvedPostIds($block->resolvedPostIds);
        }
    }
}
