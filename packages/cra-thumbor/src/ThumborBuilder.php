<?php

/**
 * @licence proprietary
 *
 * @author  Olga Kiriliuk <fialka.chan@gmail.com>
 */

namespace Cra\Thumbor;

use Thumbor\Url\BuilderFactory;
use Scm\Tools\Logger;

/**
 * Thumbor builder class. Initiates Thumbor and builds URLs.
 */
class ThumborBuilder
{
    /**
     * @var string[]
     */
    protected array $extensions = [
        'gif',
        'jpg',
        'jpeg',
        'png',
    ];

    private string $server;
    private string $secret;

    /**
     * Constructor. Sets configs to init Thumbor.
     *
     * @param string $server
     * @param string $secret
     */
    public function __construct(string $server, string $secret)
    {
        $this->server = $server;
        $this->secret = $secret;
    }

    /**
     * Build Thumbor URL for given image URL.
     *
     * @param string $imageUrl
     * @param array<string, mixed> $builderArgs
     *
     * @return string
     */
    public function buildUrl(string $imageUrl, array $builderArgs): string
    {
        $thumbnailUrlFactory = BuilderFactory::construct($this->server, $this->secret);

        $urlWithoutScheme = preg_replace('(^https?://?)', '', $imageUrl);
        $imageUrlBuilder = $thumbnailUrlFactory->url($urlWithoutScheme ?? $imageUrl);

        if (isset($builderArgs['fit'])) {
            $imageUrlBuilder = $imageUrlBuilder->fitIn($builderArgs['fit']['width'], $builderArgs['fit']['height']);
        }

        if (isset($builderArgs['crop'])) {
            $imageUrlBuilder = $imageUrlBuilder->resize($builderArgs['crop']['width'], $builderArgs['crop']['height']);
        }

        if (isset($builderArgs['smart_crop'])) {
            $imageUrlBuilder->smartCrop(apply_filters('cra_thumbor_smart_crop', $builderArgs['smart_crop']));
        }

        return (string)$imageUrlBuilder;
    }

    /**
     * Ensure image URL is valid.
     *
     * @param string $url
     *
     * @return bool
     */
    public function validateImageUrl(string $url): bool
    {
        // If the image already went through Thumbor, we need to stop processing.
        if (str_contains($url, $this->server)) {
            Logger::log("Thumbor: Attempt to double-process URL. URL: " . $url, 'error');
            return false;
        }

        $parsed_url = @parse_url($url);

        if (!$parsed_url) {
            Logger::log("Thumbor: Failed to parse URL. URL: " . $url, 'error');
            return false;
        }

        // Parse URL and ensure needed keys exist, since the array returned
        // by `parse_url` only includes the URL components it finds.
        $url_info = wp_parse_args($parsed_url, [
            'scheme' => null,
            'host' => null,
            'port' => null,
            'path' => null,
        ]);

        // Bail if $url_info isn't complete.
        if (is_null($url_info['host']) || is_null($url_info['path'])) {
            Logger::log("Thumbor: Image URL is missing host or path. URL: " . $url, 'error');
            return false;
        }

        // Ensure image extension is acceptable.
        if (!in_array(strtolower(pathinfo($url_info['path'], PATHINFO_EXTENSION)), $this->extensions)) {
            Logger::log("Thumbor: Image has non-acceptable extension. URL: " . $url, 'error');
            return false;
        }

        // Check for bad scheme: get rid of correct scheme and then check if bad one is there.
        $replacedUrl = preg_replace('(^https?://)', '', $url);
        if (!$replacedUrl || str_contains($replacedUrl, 'https:/') || str_contains($replacedUrl, 'https%3A/')) {
            Logger::log("Thumbor: Image URL has non-acceptable scheme. URL: " . $url, 'error');
            return false;
        }

        // If we got this far, we should have an acceptable image URL
        // But let folks filter to decline if they prefer.
        return apply_filters('cra_thumbor_validate_image_url', true, $url, $parsed_url);
    }
}
