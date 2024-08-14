<?php

/**
 * Main plugin file
 *
 * @package   Cra\CtLanding
 * @author    Squiz Pty Ltd <products@squiz.net>
 * @copyright 2021 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   proprietary
 */

declare(strict_types=1);

namespace Cra\CtLanding;

use BrightNucleus\Config\ConfigInterface;
use BrightNucleus\Config\ConfigTrait;
use BrightNucleus\Config\Exception\FailedToProcessConfigException;
use Cra\BlemmyaeApplications\BlemmyaeApplications;
use WP_Post;

/**
 * Main plugin class.
 *
 * @since 0.1.0
 *
 * @package Cra\CtLanding
 * @author  Gary Jones
 */
class Plugin
{
    use ConfigTrait;

    /**
     * Static instance of the plugin.
     *
     * @since 0.1.0
     *
     * @var self
     */
    protected static Plugin $instance;

    /**
     * Instantiate a Plugin object.
     *
     * Don't call the constructor directly, use the `Plugin::get_instance()`
     * static method instead.
     *
     * @param ConfigInterface $config Config to parametrize the object.
     *
     * @throws FailedToProcessConfigException If the Config could not be parsed correctly.
     *
     * @since 0.1.0
     *
     */
    public function __construct(ConfigInterface $config)
    {
        $this->processConfig($config);
    }

    /**
     * Launch the initialization process.
     *
     * @since 0.1.0
     */
    public function run(): void
    {
        add_action('plugins_loaded', [$this, 'loadTextDomain']);

        // Add prefix to slug for desired slug.
        // We need to be sure that we build slug for any post status => we need to use
        // wp_unique_post_slug to override slug pattern.
        add_filter('wp_unique_post_slug', [$this, 'attachAppsPrefixToSlug'], 10, 4);

        // Save link with prefix into DB on post insert.
        add_action('save_post', [$this, 'savePost'], 10, 2);

        new Landing();
        new LandingCT();
    }


    /**
     * Alter save post action.
     *
     * @param int $postId
     * @param WP_Post | null $post
     *
     * @return void
     */
    public function savePost(int $postId, ?WP_Post $post = null): void
    {
        // Work only with landings.
        if (get_post_type($postId) !== LandingCT::POST_TYPE) {
            return;
        }

        if (!$post) {
            $postData = get_post($postId);
            $post = is_array($postData) ? $postData[0] : $postData;
            if (is_null($post)) {
                return;
            }
        }

        // Attach prefix for Landings.
        if (!empty($post->post_name) && !wp_is_post_revision($postId)) {
            // Unhook this function to prevent infinite looping.
            remove_action('save_post', [$this, 'savePost']);

            // Update slug.
            $slug = $this->attachAppsPrefixToSlug(
                $post->post_name,
                $postId,
                $post->post_status,
                $post->post_type
            );

            // Update the post slug.
            wp_update_post([
                'ID' => $postId,
                'post_name' => $slug,
            ]);

            // Re-hook this function.
            add_action('save_post', [$this, 'savePost']);
        }
    }

    /**
     * Attach apps prefix to post slug.
     *
     * It works only for Landings.
     *
     * @param string $slug
     *   Slug.
     * @param int $postId
     *   Post id.
     * @param string $postType
     *   Post type.
     *
     * @return string
     *   Return updated slug.
     */
    public function attachAppsPrefixToSlug(string $slug, int $postId, string $postStatus, string $postType): string
    {
        // Attach prefix only for Landings.
        if ($postType === LandingCT::POST_TYPE) {
            // Get application term.
            $application = get_field('application', $postId);

            // Get app prefix from slug.
            $appFromSlug = BlemmyaeApplications::getAppsNameByLandingPath($slug);

            // Add prefix to slug based on value from application field.
            if ($application && $application->slug !== $appFromSlug) {
                // Remove old apps prefix from the url.
                $newSlug = str_replace(
                    BlemmyaeApplications::urlPrefixByApps($appFromSlug),
                    '',
                    $slug
                );

                // Get app prefix based on term & build new slug.
                $newSlug = BlemmyaeApplications::urlPrefixByApps($application->slug) . $newSlug;
            }
        }

        return $newSlug ?? $slug;
    }

    /**
     * Load the plugin text domain.
     *
     * @since 0.1.0
     */
    public function loadTextDomain(): void
    {
        /**
         * Plugin text domain.
         *
         * @var string $textDomain
         */
        $textDomain = $this->config->getKey('Plugin.textdomain');
        $languagesDir = 'languages';
        if ($this->config->hasKey('Plugin/languages_dir')) {
            /**
             * Directory path.
             *
             * @var string $languagesDir
             */
            $languagesDir = $this->config->getKey('Plugin.languages_dir');
        }

        load_plugin_textdomain($textDomain, false, $textDomain . '/' . $languagesDir);
    }
}
