<?php

/**
 * AbstractBlock class, adds necessary fields and methods
 * to all descents - ContentTeaserBlock and Block
 *
 * @author   Nikita Sokolskiy (n_sokolskiy@dotwrk.com)
 */

declare(strict_types=1);

namespace Cra\BlemmyaeBlocks\Block;

/**
 * AbstractBlock class
 */
abstract class AbstractTermBlock extends AbstractBlock
{
    /**
     * @var string[]
     */
    public array $taxonomy;

    /**
     * @var array<int, mixed>
     */
    public array $termQueryGroups;

    /**
     * @var int[]
     */
    public array $resolvedPostIds = [];

    /**
     * @var string[]|null
     */
    public ?array $path;

    public int $weight = 0;

    /**
     * @param array<string, mixed> $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }
}
