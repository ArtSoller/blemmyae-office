<?php

/**
 * @licence proprietary
 *
 * @author  Olga Kiriliuk <fialka.chan@gmail.com>
 */

namespace Cra\Thumbor;

/**
 * Thumbor class.
 */
class Thumbor
{
    private ThumborBuilder $builder;

    public function __construct()
    {
        // @phpstan-ignore-next-line
        $this->builder = new ThumborBuilder(untrailingslashit(THUMBOR_SERVER), THUMBOR_SECRET);
    }

    /**
     * Gets Thumbor URL for given image and parameters.
     *
     * @param string $image_url
     * @param int $width
     * @param int $height
     * @param bool $crop
     *
     * @return string
     **/
    public function getThumborImageUrl(string $image_url, int $width = 0, int $height = 0, bool $crop = false): string
    {
        // Expose determined arguments to a filter.
        $transform = $crop ? 'crop' : 'fit';

        $builderArgs = [];
        if ($width || $height) {
            $builderArgs[$transform] = [
                'width' => $width,
                'height' => $height,
            ];
        }

        // Allow with a filter to turn on smart cropping for all images.
        $builderArgs['smart_crop'] = apply_filters('cra_thumbor_builder_smart_crop', true, $image_url, $builderArgs);

        // Let people filter the args.
        $builderArgs = apply_filters('cra_thumbor_builder_args', $builderArgs, $image_url);

        // Check if image URL should be used.
        if (!$this->builder->validateImageUrl($image_url)) {
            return $image_url;
        }

        return $this->builder->buildUrl($image_url, $builderArgs);
    }
}
