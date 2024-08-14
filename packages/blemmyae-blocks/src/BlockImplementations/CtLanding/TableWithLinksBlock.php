<?php

/**
 * TableWithLinksBlock class, a term block class corresponding to
 * Table With Links collection widget block
 *
 * @author   Nikita Sokolskiy (n_sokolskiy@dotwrk.com)
 */

declare(strict_types=1);

namespace Cra\BlemmyaeBlocks\BlockImplementations\CtLanding;

use Cra\BlemmyaeBlocks\Block\AbstractTermBlock;
use Cra\BlemmyaeBlocks\Block\BlockQueue;
use Exception;
use Scm\WP_GraphQL\Taxonomy;

/**
 * TableWithLinksBlock class
 */
class TableWithLinksBlock extends AbstractTermBlock
{
    /**
     * @inheritDoc
     */
    public function init(
        array $block,
        array $path = [],
        ?BlockQueue $queue = null,
        string $applicationSlug = ''
    ): void {
        $this->path = $path;
        $this->termQueryGroups = $block['groups'];
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function resolvePostIds(): void
    {
        foreach ($this->termQueryGroups as $group) {
            $groupTerms = Taxonomy::taxonomyTermsByDepthResolver([
                'taxonomy' => $group['taxonomy_query']['taxonomy']->name ?? null,
                'depth' => (int)($group['taxonomy_query']['delta'] ?? 0),
            ]);
            $this->resolvedPostIds = [
                ...$this->resolvedPostIds,
                ...array_map(static fn($term) => $term->term_id, $groupTerms),
            ];
        }
    }

    /**
     * @inheritDoc
     */
    public function excludePostIds(): array
    {
        // @todo: currently there are no other term blocks, update later
        return [];
    }
}
