<?php

/**
 * AbstractBlock class, adds necessary fields and methods
 * to all descents - ContentTeaserBlock and Block
 *
 * @author   Nikita Sokolskiy (n_sokolskiy@dotwrk.com)
 */

declare(strict_types=1);

namespace Cra\BlemmyaeBlocks\Block;

use Exception;
use ValueError;

/**
 * AbstractBlock class
 */
abstract class AbstractBlock
{
    /**
     * @var array<string, mixed>
     */
    protected array $config;

    /**
     * Block's name.
     *
     * @var string
     */
    public string $name;

    /**
     * Init block.
     *
     * @param array<string, mixed> $block
     * @param string[] $path
     * @param BlockQueue|null $queue
     * @param string $applicationSlug
     *
     * @return void
     * @throws Exception
     * @throws ValueError
     */
    abstract public function init(
        array $block,
        array $path = [],
        ?BlockQueue $queue = null,
        string $applicationSlug = ''
    ): void;

    /**
     * Resolves posts according to block data, then gets post ids list
     * with injected non-dfp natives, if corresponding option is set.
     *
     * @return void
     */
    abstract public function resolvePostIds(): void;

    /**
     * Returns array of post IDs, that were already processed during
     * resolving. Used to exclude posts that are already present on the
     * page from being returned as part of any other block.
     *
     * @return int[]
     */
    abstract public function excludePostIds(): array;
}
