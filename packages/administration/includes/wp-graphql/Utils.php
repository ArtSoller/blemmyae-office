<?php

/**
 * @file Class Utils which contains misc functions related to WPGraphQL and its extensions.
 */

declare(strict_types=1);

namespace Scm\WP_GraphQL;

/**
 * Class Utils.
 *
 * @package Scm\WP_GraphQL
 */
class Utils
{
    private const WP_GRAPHQL_GUTENBERG_BLOCKS_OPTION_NAME = 'wp_graphql_gutenberg_block_types';

    public static function importGutenbergBlocksDefinitions(string $path): void
    {
        $newGutenbergBlocksDefinitions = json_decode(file_get_contents($path), true);
        $existingGutenbergBlockDefinitions = get_option(
            self::WP_GRAPHQL_GUTENBERG_BLOCKS_OPTION_NAME,
            []
        );
        foreach ($newGutenbergBlocksDefinitions as $name => $definition) {
            if (!isset($existingGutenbergBlockDefinitions[$name])) {
                $existingGutenbergBlockDefinitions[$name] = $definition;
            }
        }
        update_option(
            self::WP_GRAPHQL_GUTENBERG_BLOCKS_OPTION_NAME,
            $existingGutenbergBlockDefinitions
        );
    }

    /**
     * FRONTEND_URI const value.
     *
     * @return string
     */
    public static function frontendUri(): string
    {
        return defined('FRONTEND_URI') ? (string)FRONTEND_URI : '';
    }

    /**
     * Get files CDN host.
     *
     * @return string
     */
    public static function filesCdnHost(): string
    {
        return defined('FILES_CDN_HOST') ? (string)FILES_CDN_HOST : 'https://files.scmagazine.com';
    }

    /**
     * WP_HEADLESS_SECRET const value.
     *
     * @return string
     */
    public static function secret(): string
    {
        return defined('WP_HEADLESS_SECRET') ? WP_HEADLESS_SECRET : '';
    }

    /**
     * Get terms of a certain nesting level for taxonomy.
     *
     * @param array $hierarchy Hierarchical taxonomy array.
     * @param int $depth Nesting level.
     *
     * @return array Terms of a provided nesting level.
     */
    public static function getTermsFromHierarchyByDepth(array $hierarchy, int $depth): array
    {
        $tempHierarchy = $hierarchy;
        while ($depth) {
            $tempHierarchy = array_reduce(
                array_column($tempHierarchy, 'children', 'term_id'),
                static function (array $collector, $children) {
                    return $collector + $children;
                },
                [],
            );
            $depth--;
        }

        return $tempHierarchy;
    }
}
