<?php

declare(strict_types=1);

namespace Scm\WP_GraphQL;

#@todo: Rename to varnish prefix class.
class GraphqlCdn
{
    /**
     * If set then ignore missing config warning.
     *
     * @return bool
     */
    private static function isVerboseVarnishCachePurge(): bool
    {
        return defined('VARNISH_VERBOSE_CACHE_PURGE') && VARNISH_VERBOSE_CACHE_PURGE;
    }

    /**
     * Class constructor.
     */
    public function __construct()
    {
        add_action(
            'graphql_purge',
            static function ($purgeKeys, $event = '', $hostname = 'varnish') {
                if (is_string($purgeKeys)) {
                    $purgeKeys = [$purgeKeys];
                }
                // A function that communicates with Varnish, Fastly, etc. to purge the tagged documents.
                self::varnishCachePurge($purgeKeys, $hostname, self::isVerboseVarnishCachePurge());
            }
        );
    }

    /**
     * Purge Varnish cache.
     *
     * @param string[] $purgeKeys
     * @param string $hostname
     * @param bool $isVerbose
     *
     * @return void
     * curl -XPURGE -H "xkey: 8155054 test tt" http://api:80
     */
    public static function varnishCachePurge(array $purgeKeys, string $hostname, bool $isVerbose = false): void
    {
        if (!$hostname || !extension_loaded('curl')) {
            return;
        }

        $header = [
            'X-GraphQL-Keys-Purge: ' . implode(' ', $purgeKeys),
        ];
        $curlOptionList = [
            CURLOPT_URL => 'http://' . $hostname,
            CURLOPT_HTTPHEADER => $header,
            CURLOPT_CUSTOMREQUEST => 'PURGE',
            CURLOPT_VERBOSE => $isVerbose,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_NOBODY => true,
            CURLOPT_CONNECTTIMEOUT_MS => 2000,
        ];
        $curlHandler = curl_init();
        curl_setopt_array($curlHandler, $curlOptionList);
        curl_exec($curlHandler);
        curl_close($curlHandler);
    }
}
