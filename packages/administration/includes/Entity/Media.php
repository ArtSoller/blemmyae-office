<?php

/**
 * @author  Konstantin Gusev <guvkon.net@icloud.com>
 * @license proprietary
 */

declare(strict_types=1);

namespace Scm\Entity;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Imagick;
use ImagickException;
use Scm\Tools\PsrLogger as Logger;
use WP_Post;

/**
 * Media entity related consts and static methods.
 */
class Media
{
    public const FIELD__ORIGINAL_SOURCE = 'field_61e529e43808a';
    public const FIELD__LQIP = 'field_62306c43cade2';

    protected const LQIP_HEIGHT = 50;
    protected const LQIP_WIDTH = 50;
    protected const LQIP_BLUR = 0.1;

    /**
     * Consumes image BLOB and generates base64 LQIP.
     *
     * @param string $fileBlobSrc
     *
     * @return string|null
     */
    protected static function createLqip(string $fileBlobSrc): ?string
    {
        if (!$fileBlobSrc) {
            return null;
        }

        $fileBlobNew = null;
        try {
            $file = new Imagick();
            // If image background is transparent, set it.
            $file->setBackgroundColor(new \ImagickPixel('transparent'));
            $file->readImageBlob($fileBlobSrc);
            // Normalise the density to XXX dpi.
            $file->setImageResolution(self::LQIP_WIDTH, self::LQIP_HEIGHT);
            $file->setImageFormat('webp');
            // Set sampling factors to constant.
            $file->setSamplingFactors(['1x1', '1x1', '1x1']);
            // Ensure we use default Huffman tables.
            $file->setOption('webp:optimize-coding', '');
            // Strip unnecessary header data.
            $file->stripImage();
            // Resize & blur.
            $file->resizeImage(
                self::LQIP_WIDTH,
                self::LQIP_HEIGHT,
                Imagick::FILTER_GAUSSIAN,
                self::LQIP_BLUR,
                true
            );
            // Debug, ex. $file->writeImage(__DIR__ . '/test.jpg')
            $fileBlobNew = $file->getImageAlphaChannel() ? null : $file->getImageBlob();
        } catch (ImagickException $error) {
            (new Logger())->error($error->getMessage(), $error->getTrace());
        }

        return $fileBlobNew ? base64_encode($fileBlobNew) : 'null';
    }

    /**
     * Populates attachments lqip field with base64 image body.
     *
     * @param WP_Post $post
     *
     * @return string|null
     */
    protected static function updateLqip(WP_Post $post): ?string
    {
        $filePath = wp_get_attachment_url($post->ID);
        if (!$filePath) {
            return null;
        }
        try {
            $client = new Client();
            $fileBlob = $client->get($filePath)->getBody()->getContents();
            $result = self::createLqip($fileBlob);
            update_field(self::FIELD__LQIP, $result, $post->ID);
            return $result;
        } catch (GuzzleException $error) {
            $logger = new Logger();
            $logger->error(
                'Failed downloading asset.',
                ["ID: {$post->ID}", "Source URL: $filePath"]
            );
            $logger->error($error->getMessage(), $error->getTrace());
        }

        return null;
    }

    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->initHooks();
    }

    /**
     * Initialize hooks.
     *
     * @return void
     * @fixme: neither `wp_add_attachment` or `save_post_attachment` actions end up being called,
     *   unable to populate fields on media upload.
     */
    public function initHooks(): void
    {
        add_filter(
            'acf/load_value/key=' . self::FIELD__LQIP,
            [$this, 'imageAdvancedLqip'],
            10,
            3
        );
        add_filter(
            'acf/update_value/key=' . self::FIELD__LQIP,
            [$this, 'updateImageAdvancedLqip'],
            10,
            4
        );
        add_filter('mime_types', [$this, 'allowedMimeTypes']);
    }

    /**
     * Returns lqip base64 body, if empty tries to re-create and return afterwards.
     *
     * @param mixed $value
     * @param mixed $postId
     * @param mixed $field
     *
     * @return mixed
     */
    public function imageAdvancedLqip($value, $postId, $field)
    {
        if (!$value) {
            $attachment = get_post($postId);
            if (($attachment instanceof WP_Post) && wp_attachment_is_image($attachment)) {
                return self::updateLqip($attachment);
            }
        }

        return $value;
    }

    /**
     * WP Hooks legacy to avoid fatals on mismatching action callback params @fixme.
     *
     * @param mixed $value
     * @param mixed $postId
     * @param mixed $field
     * @param mixed $original
     *
     * @return mixed
     */
    public function updateImageAdvancedLqip($value, $postId, $field, $original)
    {
        return $this->imageAdvancedLqip($value, $postId, $field);
    }

    /**
     * Add allowed MIME types.
     *
     * @param array $types
     *
     * @return array
     */
    public function allowedMimeTypes(array $types): array
    {
        return $types + [
            'json' => 'application/json',
            'svg' => 'image/svg+xml'
        ];
    }
}
