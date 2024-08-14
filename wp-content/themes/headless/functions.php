<?php // phpcs:disable PSR1.Files.SideEffects.FoundWithSymbols
/*
 *  Author: Alexander Kucherov <avdkucherov@gmail.com>
 *  Custom functions, support and more.
 */

declare(strict_types=1);

// phpcs:disable NeutronStandard.Globals.DisallowGlobalFunctions.GlobalFunctions
add_theme_support('post-thumbnails');

/**
 * Disables rss feed.
 */
function headlessDisableFeed(): void
{
    // phpcs:ignore
    wp_die(__('No feed available, please visit our <a href="' . get_bloginfo('url') . '">homepage</a>!'));
}

add_action('do_feed_rdf', 'headlessDisableFeed', 1);
add_action('do_feed_rss', 'headlessDisableFeed', 1);
add_action('do_feed_rss2', 'headlessDisableFeed', 1);
add_action('do_feed_atom', 'headlessDisableFeed', 1);
add_action('do_feed_rss2_comments', 'headlessDisableFeed', 1);
add_action('do_feed_atom_comments', 'headlessDisableFeed', 1);

/**
 * Init theme's menu support.
 *
 * List legacy of menus still in db.
 * 'other-sc-sites-footer' => 'Other SC Sites Footer',
 * 'product-reviews-footer' => 'Product Reviews Footer',
 * 'secondary' => 'Secondary',
 * 'secondary-left' => 'Secondary Left',
 * 'user-center-footer' => 'User Center Footer'
 */
add_action(
    'after_setup_theme',
    // phpcs:ignore Inpsyde.CodeQuality.LineLength.TooLong
    static fn() => register_nav_menus(['company-info-footer' => 'Company Info Footer', 'primary' => 'Primary'])
);

/**
 * Reduce amount of image resizes for media.
 */
function remove_select_image_sizes($sizes)
{
    foreach (get_intermediate_image_sizes() as $size) {
        if (!in_array($size, ['thumbnail', 'medium'])) {
            unset($sizes[$size]);
        }
    }

    return $sizes;
}

/**
 * Filters the image sizes automatically generated when uploading an image.
 *
 * Weight 210 - to be called after EWWW IO filters.
 */
add_filter('intermediate_image_sizes_advanced', 'remove_select_image_sizes', 210);
