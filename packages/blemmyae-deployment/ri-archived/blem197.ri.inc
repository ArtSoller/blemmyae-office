<?php

/**
 * @file Add core "WP Graphql Gutenberg Block Types"
 *
 * @link https://cra.myjetbrains.com/youtrack/issue/BLEM-197
 */

declare(strict_types=1);

use Scm\WP_GraphQL\Utils;

/**
 * Add core "WP Graphql Gutenberg Block Types".
 */
function blem197_blemmyae_deployment_ri_001(): void
{
    if (!class_exists(Utils::class)) {
        return;
    }

    Utils::importGutenbergBlocksDefinitions(
        dirname(__DIR__) . '/config/graphql/core_gutenberg_blocks.json'
    );
}
