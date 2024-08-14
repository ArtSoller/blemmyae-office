<?php

/**
 * @link: https://cra.myjetbrains.com/youtrack/issue/BLEM-124
 * @phpcs:disable NeutronStandard.Globals.DisallowGlobalFunctions
 */

declare(strict_types=1);

use Cra\BlemmyaeAds\Ads;

/**
 * Import entity configs.
 */
function blemmyae_ads_ri_001(): void
{
    $ads = new Ads(dirname(__DIR__));
    $ads->registerOptionsPage();
    wp_cache_flush();
}

/**
 * Re-sets`blemmyae_ads_ri_001`.
 */
function blemmyae_ads_ri_003(): void
{
    // Intentionally empty.
}

/**
 * Remove ACF AdSlot block and related field group.
 */
function blemmyae_ads_ri_004(): void
{
    // Remove "Ad Slot" field group.
    acf_delete_field_group('group_60db14121bf26');

    // Remove "Ad Slot" block type.
    $blockAcf = acf_get_instance('acfe_dynamic_block_types');
    if ($blockAcf instanceof \acfe_dynamic_block_types) {
        $post = get_page_by_path('adslot', OBJECT, $blockAcf->post_type);
        if ($post) {
            $blockAcf->trashed_post($post->ID);
            wp_delete_post($post->ID, true);
        }
    }

    // Remove GQL definition.
    $definitions = array_filter(
        get_option('wp_graphql_gutenberg_block_types', []),
        static fn($definition) => $definition['name'] !== 'acf/adslot'
    );
    update_option('wp_graphql_gutenberg_block_types', $definitions);
}
