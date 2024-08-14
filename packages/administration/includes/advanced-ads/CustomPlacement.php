<?php // phpcs:ignore PSR1.Files.SideEffects.FoundWithSymbols

/**
 * Advanced Ads – Custom Hook Placement.
 *
 * @author Alexander Kucherov (avdkucherov@gmail.com)
 */

declare(strict_types=1);

namespace Scm\Advanced_Ads;

// If this file is called directly, abort.
use Advanced_Ads_Admin_Options;
use Advanced_Ads_Placements;

if (!defined('WPINC')) {
    die;
}

/**
 * Define action hooks.
 */
define('CUSTOM_PLACEMENT_HOOK_NAME', 'ampforwp_before_post_content');
define('CUSTOM_PLACEMENT_HOOK_TYPE', 'custom_hook_ampforwp');

class CustomPlacement
{
    /**
     * Initialize hooks.
     */
    public function __construct()
    {
        add_action('plugins_loaded', [$this, 'pluginsLoadedAdActions']);
        add_action('plugins_loaded', [$this, 'adminPluginsLoaded']);
        add_action('advanced-ads-placement-options-after', [$this, 'placementOptions'], 20, 2);
        add_filter('the_content', [$this, 'injectContent'], 100);
    }

    /**
     * Initialize this add-on.
     */
    public function pluginsLoadedAdActions(): void
    {
        // Stop, if main plugin doesn’t exist.
        if (!class_exists('Advanced_Ads', false)) {
            return;
        }
        // Load the dynamic hook.
        $placements = get_option('advads-ads-placements', []);

        foreach ($placements as $_placement) {
            if (CUSTOM_PLACEMENT_HOOK_TYPE === ($_placement['type'] ?? '')) {
                add_action(CUSTOM_PLACEMENT_HOOK_NAME, 'executeHook');
            }
        }
    }

    /**
     * Hook to alter.
     */
    public function executeHook(): void
    {
        // Get placements.
        $placements = get_option('advads-ads-placements', []);

        // Look for the current hook in the placements.
        foreach ($placements as $_placement_id => $_placement) {
            if (
                CUSTOM_PLACEMENT_HOOK_TYPE === ($_placement['type'] ?? '')
                && current_filter() === CUSTOM_PLACEMENT_HOOK_NAME
            ) {
                // the_ad_placement( $_placement_id );
                echo \Advanced_Ads_Select::get_instance()->get_ad_by_method(
                    $_placement_id,
                    \Advanced_Ads_Select::PLACEMENT,
                    $_options = $_placement['options'] ?? []
                );
            }
        }
    }

    /**
     * Backend functions.
     */
    public function adminPluginsLoaded(): void
    {
        if (!class_exists('Advanced_Ads_Admin', false)) {
            return;
        }

        // Add custom hook placement.
        add_action('advanced-ads-placement-types', [$this, 'addPlacement']);
    }

    /**
     * Add placement.
     * @param $types
     *  Array of placement types.
     * @return mixed
     *  Array of placement types.
     */
    public function addPlacement($types): mixed
    {
        // Placement title and description.
        $types[CUSTOM_PLACEMENT_HOOK_TYPE] = array_merge(
            $types['post_content'],
            [
                'title' => __(
                    $types['post_content']['title'] . ' Extended',
                    'advanced-ads',
                ),
                'description' => __(
                    $types['post_content']['description'] . ' May be filtered by topics.',
                    'advanced-ads',
                ),
                'image' => ADVADS_BASE_URL . 'admin/assets/img/placements/custom-position.png',
            ]
        );

        return $types;
    }

    /**
     * Injected ad into content (before and after).
     * Displays ALL ads.
     *
     * @param $content
     *  Page content.
     * @return string|null
     *  Page content.
     */
    public function injectContent($content): ?string
    {
        // Stop, if main plugin doesn’t exist or no content to inject into.
        if (!class_exists('Advanced_Ads', false) || !$content) {
            return $content;
        }

        $placements = get_option('advads-ads-placements', []);

        if (!apply_filters('advanced-ads-can-inject-into-content', true, $content, $placements)) {
            return $content;
        }

        if (is_array($placements)) {
            foreach ($placements as $_placement_id => $_placement) {
                if (empty($_placement['item']) || !isset($_placement['type'])) {
                    continue;
                }
                $_options = $_placement['options'] ?? [];
                $_topics = $_options['topics'] ?? [];
                $_editorial_types = $_options['editorial-types'] ?? [];
                $_post_types = $_options['post-types'] ?? [];

                // Check if injection is ok for a specific placement ID.
                if (
                    !apply_filters(
                        'advanced-ads-can-inject-into-content-' . $_placement_id,
                        true,
                        $content,
                        $_placement_id
                    )
                ) {
                    continue;
                }
                $content = Advanced_Ads_Placements::inject_in_content(
                    $_placement_id,
                    $_options,
                    $content
                );
                // Render ad(s).
                if (
                    $_placement['type'] === CUSTOM_PLACEMENT_HOOK_TYPE &&
                    (!$_topics || array_intersect(
                        wp_get_post_terms(get_the_ID(), 'topic', ['fields' => 'ids']),
                        $_topics
                    )) && (!$_editorial_types || array_intersect(
                        wp_get_post_terms(get_the_ID(), 'editorial_type', ['fields' => 'ids']),
                        $_editorial_types
                    )) && (!$_post_types || array_intersect(
                        [get_post_type(get_the_ID())],
                        $_editorial_types
                    ))
                ) {
                    $content = Advanced_Ads_Placements::inject_in_content(
                        $_placement_id,
                        $_options,
                        $content
                    );
                }
            }
        }

        return $content;
    }

    /**
     * Renders extended placement options.
     *
     * @param $_placement_slug
     *  Slug of the current placement.
     * @param $_placement
     *  Information of the current placement.
     */
    public function placementOptions($_placement_slug, $_placement): void
    {
        if ($_placement['type'] === CUSTOM_PLACEMENT_HOOK_TYPE) {
            // No ability to extend this functionality, so duplicating this code block from source AS IS.
            // Copy - start.
            $option_index = isset($_placement['options']['index']) ? absint(
                max(1, (int)$_placement['options']['index'])
            ) : 1;
            $option_tag = $_placement['options']['tag'] ?? 'p';

            // Automatically select the 'custom' option.
            if (!empty($_COOKIE['advads_frontend_picker'])) {
                $option_tag = ($_COOKIE['advads_frontend_picker'] === $_placement_slug) ? 'custom' : $option_tag;
            }

            $option_xpath = isset($_placement['options']['xpath']) ? stripslashes($_placement['options']['xpath']) : '';
            $positions = [
                'after' => __('after', 'advanced-ads'),
                'before' => __('before', 'advanced-ads'),
            ];
            ob_start();
            include ADVADS_BASE_PATH . 'admin/views/placements-content-index.php';
            if (!defined('AAP_VERSION')) {
                include ADVADS_BASE_PATH . 'admin/views/upgrades/repeat-the-position.php';
            }

            do_action(
                'advanced-ads-placement-post-content-position',
                $_placement_slug,
                $_placement
            );
            $option_content = ob_get_clean();

            Advanced_Ads_Admin_Options::render_option(
                'placement-content-injection-index',
                __('position', 'advanced-ads'),
                $option_content
            );
            // Copy - end.

            // Custom code.
            $option_topics = $_placement['options']['topics'] ?? [];
            ob_start();
            include plugin_dir_path(__FILE__) . 'views/placement-content-topic-filter.php';
            $option_content = ob_get_clean();

            Advanced_Ads_Admin_Options::render_option(
                'placement-content-topic-filter',
                __('topic-filter', 'advanced-ads'),
                $option_content
            );

            $option_post_types = $_placement['options']['post-types'] ?? [];
            ob_start();
            include plugin_dir_path(__FILE__) . 'views/placement-content-post-type-filter.php';
            $option_content = ob_get_clean();

            Advanced_Ads_Admin_Options::render_option(
                'placement-content-post-type-filter',
                __('post-type-filter', 'advanced-ads'),
                $option_content
            );

            $option_editorial_types = $_placement['options']['editorial-types'] ?? [];
            ob_start();
            include plugin_dir_path(__FILE__) . 'views/placement-content-editorial-type-filter.php';
            $option_content = ob_get_clean();

            Advanced_Ads_Admin_Options::render_option(
                'placement-content-editorial-type-filter',
                __('editorial-type-filter', 'advanced-ads'),
                $option_content
            );
        }
    }
}
