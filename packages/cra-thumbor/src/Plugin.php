<?php

// TODO: thoroughly test with the line below uncommented.
// declare(strict_types=1);

/**
 * Main plugin file
 *
 * @package   Cra\Thumbor
 * @author    Squiz Pty Ltd <products@squiz.net>
 * @copyright 2021 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   proprietary
 */

namespace Cra\Thumbor;

use BrightNucleus\Config\ConfigInterface;
use BrightNucleus\Config\ConfigTrait;
use BrightNucleus\Config\Exception\FailedToProcessConfigException;
use BrightNucleus\Settings\Settings;
use WP_Post;

use function add_action;
use function load_plugin_textdomain;

/**
 * Main plugin class.
 *
 * @since 0.1.0
 *
 * @package Cra\Thumbor
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
        // Load plugin's textdomain.
        add_action('plugins_loaded', [$this, 'loadTextDomain']);

        // Update image srcset calculations.
        add_filter('wp_calculate_image_srcset_meta', [$this, 'calculateImageSrcsetMeta'], 100, 4);
        add_filter('wp_calculate_image_srcset', [$this, 'calculateImageSrcset'], 100, 5);

        // Add Thumbor sources.
        add_filter('image_downsize', [$this, 'imageDownsize'], 10, 3);
        add_filter('wp_prepare_attachment_for_js', [$this, 'prepareAttachmentForJS'], 100, 3);

        // Don't generate image sizes.
        add_filter('intermediate_image_sizes_advanced', '__return_false');
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

    /**
     * Erase intermediate filenames from image meta.
     *
     * @param array<string, mixed> $image_meta
     * @param int[] $size_array
     * @param string $image_src
     * @param string|int $attachment_id
     *
     * @filter wp_calculate_image_srcset_meta
     *
     * @return array<string, mixed>
     */
    public function calculateImageSrcsetMeta(
        array $image_meta,
        array $size_array,
        string $image_src,
        string|int $attachment_id
    ): array {
        foreach ($image_meta['sizes'] as $key => $size) {
            $image_meta['sizes'][$key]['file'] = '';
        }

        return $image_meta;
    }

    /**
     * Build image's 'srcset' sources with Thumbor URLs.
     *
     * @param array<string|int, mixed> $sources
     * @param array{0: int, 1: int} $size_array
     * @param string $image_src
     * @param array<string, mixed> $image_meta
     * @param string|int $attachment_id
     *
     * @filter wp_calculate_image_srcset
     *
     * @return array<string|int, mixed>
     */
    public function calculateImageSrcset(
        array $sources,
        array $size_array,
        string $image_src,
        array $image_meta,
        string|int $attachment_id
    ): array {
        $sizesByWidth = [];
        $attachment_id = (int) $attachment_id;

        // Get the image URL.
        $imageUrl = wp_get_attachment_url($attachment_id);
        if (!$imageUrl) {
            return $sources;
        }

        // Get an array of image sizes with same ratio.
        foreach ($image_meta['sizes'] as $name => $size) {
            if (wp_image_matches_ratio($size_array[0], $size_array[1], $size['width'], $size['height'])) {
                $sizesByWidth[$size['width']] = [
                    'width' => $size['width'],
                    'height' => $size['height'],
                    'crop' => ($name == 'thumbnail'),
                ];
            }
        }

        // Replace sources' URLs with Thumbor URLs.
        foreach ($sources as $key => &$value) {
            $thumbor = new Thumbor();
            if (isset($sizesByWidth[$key])) {
                $size = $sizesByWidth[$key];
                $value['url'] = $thumbor->getThumborImageUrl(
                    $imageUrl,
                    $size['width'] ?? 0,
                    $size['height'] ?? 0,
                    $size['crop']
                );
            }
        }

        return $sources;
    }

    /**
     * Replaces downsized image URLs with Thumbor URLs.
     *
     * @param array<int|string, mixed>|bool $image
     * @param string|int $attachmentId
     * @param string|int[] $size
     *
     * @filter image_downsize
     *
     * @return array<int|string, mixed>|bool
     */
    public function imageDownsize(array|bool $image, string|int $attachmentId, string|array $size): array|bool
    {
        $attachmentId = (int) $attachmentId;

        // Provide plugins a way of preventing this plugin from being applied to images.
        if (apply_filters('cra_thumbor_override_image_downsize', false, compact('image', 'attachmentId', 'size'))) {
            return $image;
        }

        // Do nothing for `full`.
        if ('full' === $size) {
            return $image;
        }

        // Get the image URL.
        $imageUrl = wp_get_attachment_url($attachmentId);

        if (!$imageUrl) {
            return $image;
        }

        $imageArgs = $this->getImageSizes();
        // If an image is requested with a size known to WordPress, use that size's settings.
        if ((is_string($size)) && array_key_exists($size, $imageArgs)) {
            $imageArgs = $imageArgs[$size];

            if (!$imageArgs['crop'] && $image_meta = wp_get_attachment_metadata($attachmentId)) {
                // Let's make sure we don't upscale images since wp never upscales them as well.
                $smaller_width = (($image_meta['width'] < $imageArgs['width']) ?
                    $image_meta['width'] : $imageArgs['width']);
                $smaller_height = (($image_meta['height'] < $imageArgs['height']) ?
                    $image_meta['height'] : $imageArgs['height']);

                // Set new width & height.
                $imageArgs['width'] = $smaller_width;
                $imageArgs['height'] = $smaller_height;
            }
        } elseif (is_array($size)) {
            // Pull width and height values from the provided array, if possible.
            $imageArgs['width'] = isset($size[0]) ? (int)$size[0] : false;
            $imageArgs['height'] = isset($size[1]) ? (int)$size[1] : false;
            $imageArgs['crop'] = false;

            // Do nothing if necessary parameters aren't passed.
            if (!$imageArgs['width'] && !$imageArgs['height']) {
                return $image;
            }
        } else {
            // Fallback for all other cases, including ACF image fields.
            $imageArgs = $imageArgs['thumbnail'];
        }

        // Generate Thumbor URL.
        $thumbor = new Thumbor();
        $thumborUrl = $thumbor->getThumborImageUrl(
            $imageUrl,
            $imageArgs['width'] ?? 0,
            $imageArgs['height'] ?? 0,
            $imageArgs['crop']
        );

        return $thumborUrl ? [
            $thumborUrl,
            $imageArgs['width'],
            $imageArgs['height'],
            $imageArgs['crop'],
        ] : $image;
    }

    /**
     * Update image links in ajax content.
     *
     * @param array<string, mixed>|false $response
     * @param WP_Post $attachment
     * @param array<string, mixed> $meta
     *
     * @filter wp_prepare_attachment_for_js
     *
     * @return array<string, mixed>|false
     */
    public function prepareAttachmentForJS(array|bool $response, WP_Post $attachment, array $meta): array|bool
    {
        if (
            !$response ||
            $response['type'] !== 'image' ||
            !isset($response['sizes']) ||
            !is_array($response['sizes'])
        ) {
            return $response;
        }

        $sizes = $this->getImageSizes();
        $thumbor = new Thumbor();

        // Replace intermediate image URLs.
        foreach ($response['sizes'] as $size => $value) {
            if ($size !== 'full') {
                $response['sizes'][$size]['url'] = $thumbor->getThumborImageUrl(
                    $response['url'],
                    $response['sizes'][$size]['width'] ?? 0,
                    $response['sizes'][$size]['height'] ?? 0,
                    $sizes[$size]['crop'] ?? true
                );
            }
        }

        return $response;
    }

    /**
     * Provide an array of available image sizes and corresponding dimensions.
     * Similar to get_intermediate_image_sizes(), but it includes image sizes' dimensions, not just their names.
     *
     * @return array<string, array<string, mixed>>
     * @uses get_option
     *
     * @global $wp_additional_image_sizes
     */
    private function getImageSizes(): array
    {
        global $_wp_additional_image_sizes;
        $sizes = get_intermediate_image_sizes();
        $images = [];

        // Populate an array matching the data structure of $_wp_additional_image_sizes,
        // so we have a consistent structure for image sizes.
        foreach ($sizes as $size) {
            if (!isset($_wp_additional_image_sizes[$size])) {
                $images[$size] = [
                    'width' => !empty(get_option($size . '_size_w')) ? intval(get_option($size . '_size_w')) : 0,
                    'height' => !empty(get_option($size . '_size_h')) ? intval(get_option($size . '_size_h')) : 0,
                    'crop' => (bool)get_option($size . '_crop'),
                ];
            }
        }

        // Add 'full' case too.
        $images['full'] = [
            'width' => null,
            'height' => null,
            'crop' => false,
        ];

        // Update class variable, merging in $_wp_additional_image_sizes if any are set.
        return is_array($_wp_additional_image_sizes) && count($_wp_additional_image_sizes) ?
            array_merge($images, $_wp_additional_image_sizes) : $images;
    }
}
