<?php

/**
 * AbstractEndpoint class, creates an interface for custom endpoints
 *
 * @author   Nikita Sokolskiy (n_sokolskiy@dotwrk.com)
 */

declare(strict_types=1);

namespace Cra\BlemmyaeBlocks\Endpoints;

use Cra\BlemmyaeBlocks\Block\BlockFactory;
use Exception;

/**
 * AbstractEndpoint class.
 */
abstract class AbstractEndpoint
{
    protected BlockFactory $blockFactory;

    /**
     * @param BlockFactory $blockFactory
     */
    abstract public function __construct(BlockFactory $blockFactory);

    /**
     * Returns field name of the node that is a connection with resolved posts
     * Example values: "resolvedPostCollection", "contentTeaserPostsConnection"
     *
     * @return string
     */
    abstract public static function endpointFeedConnectionTitle(): string;

    /**
     * Injects dfp natives in wp graphql response
     *
     * @param array<string, mixed> $connection
     * @param string[] $path
     *
     * @return array<string, mixed>
     * @throws Exception
     */
    abstract public function injectDfpNativesTeaser(array $connection, array $path): array;

    /**
     * @return void
     */
    abstract protected function registerTypes(): void;

    /**
     * @return void
     */
    abstract protected function registerFields(): void;
}
