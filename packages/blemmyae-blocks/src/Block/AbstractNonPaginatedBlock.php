<?php

/**
 * Block class. Used with collection widget blocks
 *
 * @author   Nikita Sokolskiy (n_sokolskiy@dotwrk.com)
 */

declare(strict_types=1);

namespace Cra\BlemmyaeBlocks\Block;

use Cra\BlemmyaeBlocks\NativeAdsManager;
use Cra\BlemmyaeBlocks\WpQuery\BlockWpQueryManager;

/**
 * Block class
 */
abstract class AbstractNonPaginatedBlock extends AbstractFeedBlock
{
    /**
     * @var array<string, mixed>
     */
    protected array $collection = [];

    /**
     * @inheritDoc
     */
    final public function resolvePostIds(): void
    {
        [
            self::$blockOptions['nonDfpNatives'] => $hasNonDfpNatives,
        ] = $this->parsedBlockOptions();

        if ($this->numberOfItems) {
            $wpQueryManager = new BlockWpQueryManager($this);
            $wpQuery = $wpQueryManager->wpQuery();

            /** @var int[] $posts */
            $posts = $wpQuery->posts;
            $posts = [...$this->resolvedPostIds, ...$posts];

            if ($hasNonDfpNatives) {
                $posts = NativeAdsManager::injectNonDfpNatives(
                    $posts,
                    $this
                );
            }

            $this->resolvedPostIds = $posts;
        }
    }

    /**
     * Returns array of ids for posts that are pinned to the block. For
     * some blocks there is a collection field containing array of posts,
     * and for some there is a post field, containing one post.
     *
     * @return int[]
     */
    protected function postCollectionIds(): array
    {
        $collectedPostIds = [];
        if (array_key_exists('collection', $this->collection) && is_array($this->collection['collection'])) {
            foreach ($this->collection['collection'] as $post) {
                if (in_array($post['post'], $this->excludePostIds(), true)) {
                    continue;
                }
                $collectedPostIds[] = $post['post'];
            }
        }
        if (array_key_exists('post', $this->collection) && $this->collection['post'] !== null) {
            if (!in_array($this->collection['post'], $this->excludePostIds(), true)) {
                $collectedPostIds[] = $this->collection['post'];
            }
        }
        return $collectedPostIds;
    }
}
