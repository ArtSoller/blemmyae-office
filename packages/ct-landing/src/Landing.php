<?php

/**
 * Landing class, defines default Page type
 *
 * @package   Cra\CtLanding
 * @author    Squiz Pty Ltd <products@squiz.net>
 * @copyright 2021 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   proprietary
 */

declare(strict_types=1);

namespace Cra\CtLanding;

use Scm\Entity\CustomPostType;
use WP_Post_Type;
use WP_Query;
use WP_Screen;

/**
 * Landing class.
 *
 * @psalm-suppress UndefinedClass
 */
class Landing extends CustomPostType
{
    /**
     * @deprecated use LandingCT
     */
    public const TYPE = 'landing';

    /**
     * Landing constructor:
     *  - Calls hook init.
     *
     * @param string $pluginDirPath Plugin dir path.
     */
    public function __construct(string $pluginDirPath = '')
    {
        parent::__construct($pluginDirPath);
        $this->hookInit();
    }

    /**
     * Registers hooks.
     *
     * @return self
     * @psalm-suppress MixedPropertyFetch, MixedArrayAccess
     */
    public function hookInit(): self
    {
        add_action('init', [$this, 'hidePostObject']);
        add_filter('get_pages', [$this, 'pageOnFront'], 10, 2);
        add_action('pre_get_posts', [$this, 'enablePageOnFrontRewrite']);
        add_filter('screen_options_show_screen', [$this, 'removeScreenOptions'], 10, 2);
        add_action('add_meta_boxes', [$this, 'hideAdvancedAdsMetaboxes'], 20);
        add_action('admin_enqueue_scripts', [$this, 'enqueueLandingAdminJs']);

        if (class_exists('ACFE')) {
            add_action('acfe/init', [$this, 'enableHybridEngine']);
        }

        return $this;
    }

    /**
     * Enqueue landing admin js
     *
     * @param $hook_suffix
     * @return void
     */
    public function enqueueLandingAdminJs($hook_suffix): void
    {
        if (in_array($hook_suffix, array('post.php', 'post-new.php'))) {
            $screen = get_current_screen();

            if (is_object($screen) && 'landing' == $screen->post_type) {
                wp_register_script(
                    'landing-admin-js',
                    plugins_url('ct-landing/build/index.js', dirname(__DIR__))
                );
                wp_enqueue_script('landing-admin-js');
            }
        }
    }

    /**
     * Hide default WP Page type.
     *
     * @psalm-suppress MixedAssignment, UndefinedClass
     */
    public function hidePostObject(): void
    {
        if (!function_exists('get_post_type_object')) {
            return;
        }
        $postType = get_post_type_object('page');
        if ($postType instanceof WP_Post_Type) {
            $postType->show_in_menu = false;
            $postType->show_in_nav_menus = false;
            $postType->exclude_from_search = true;
            $postType->public = false;
            $postType->show_ui = false;
            $postType->show_in_rest = false;
            if (class_exists('WPGraphQL')) {
                $postType->show_in_graphql = false;
            }
        }
    }

    /**
     * Updates list of pages available as a frontpage.
     *
     * @param array $pages
     * @param array $request
     *
     * @return array
     */
    public function pageOnFront(array $pages, array $request): array
    {
        if (!empty($request['name'])) {
            if (in_array($request['name'], ['page_on_front', 'page_for_posts'], true)) {
                $args = [
                    'post_type' => 'landing',
                ];
                $pages = get_posts($args);
            }
        }

        return $pages;
    }

    /**
     * Fixes frontpage url rewrite for custom CT.
     *
     * @param WP_Query $query
     */
    public function enablePageOnFrontRewrite(WP_Query $query): void
    {
        // Only act on main query.
        if (!$query->is_main_query()) {
            return;
        }

        if (!empty($query->query_vars['post_type']) && !empty($query->query_vars['page_id'])) {
            $query->query_vars['post_type'] = ['landing'];
        }
    }

    /**
     * Hide sidebar blocks for landing and sc_award_nominee content types.
     *
     * @param bool $showScreen
     * @param WP_Screen $screen
     *
     * @return bool
     */
    public function removeScreenOptions(bool $showScreen, WP_Screen $screen): bool
    {
        if (in_array($screen->id, ['landing', 'sc_award_nominee'], true)) {
            global $wp_meta_boxes;
            // Only the 'publish' block should be populated in sidebar.
            $wp_meta_boxes[$screen->id]['side']['core'] = [
                'submitdiv' => $wp_meta_boxes[$screen->id]['side']['core']['submitdiv'],
            ];
        }

        return $showScreen;
    }

    /**
     * Hide ad settings block from sidebar.
     *
     * @return void
     */
    public function hideAdvancedAdsMetaboxes(): void
    {
        $screen = get_current_screen();
        if (!$screen) {
            return;
        }

        // Hide the "Ad Settings" meta box.
        remove_meta_box('advads-ad-settings', $screen->id, 'side');
    }

    /**
     * Enable ACF Hybrid Engine on Landings.
     *
     * @return void
     */
    public function enableHybridEngine(): void
    {
        // enable performance mode with config
        acfe_update_setting('modules/performance', [
            'engine' => 'hybrid',
            'ui' => true,
            'mode' => 'production',
            'post_types' => [LandingCT::POST_TYPE],
        ]);
    }
}
