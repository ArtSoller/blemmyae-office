<?php

/**
 * @license PROPRIETARY
 *
 * @author  Pavel Lovkiy <pavel.lovkiy@gmail.com>
 * @author  CRA
 */

namespace Scm\Entity\Sitemap;

use Spatie\Async\Pool;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;

/**
 * Submit sitemaps to search engines.
 *
 * Class Submitter
 */
class Submitter
{
    /**
     * Array of search engines to submit sitemaps for.
     *
     * @var array|string[]
     */
    private array $enginePingURLs = [
        'google' => "https://www.google.com/ping?sitemap=",
    ];

    /**
     * @var Pool
     */
    private Pool $pool;

    /**
     * Array of true/false values for sitemaps (true if sitemap was successfully submitted).
     * Being cleaned up before new URLs submit.
     *
     * @var array
     */
    private array $responses;

    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->pool = Pool::create()->timeout(600);
        $this->responses = [];
    }

    /**
     * Notify search engines about passed sitemaps.
     *
     * @param array $sitemapURLs Array of sitemaps to submit: ['index' => 'url', 'sitemaps' => [0 => 'url', ...]].
     * @param bool $pingSingleSitemaps Whether to notify every single sitemap or just index.
     *
     * @return void Number of submitted sitemaps.
     */
    public function submitSitemaps(array $sitemapURLs, bool $pingSingleSitemaps = true): void
    {
        $this->responses = [];
        foreach ($this->enginePingURLs as $engine) {
            try {
                $this->responses[$sitemapURLs['index']] = self::pingSitemap($engine, $sitemapURLs['index']);
            } catch (ExceptionInterface $err) {
                Logger::log(
                    sprintf(
                        'Error submitting sitemap %s to %s. Exception: %s',
                        $sitemapURLs['index'],
                        $engine,
                        $err->getMessage()
                    ),
                    'warning'
                );
                $this->responses[$sitemapURLs['index']] = false;
            }
            if ($pingSingleSitemaps) {
                foreach ($sitemapURLs['sitemaps'] as $url) {
                    $this->pool
                        ->add(static function () use ($engine, $url) {
                            self::pingSitemap($engine, $url);
                        })
                        ->then(function ($success) use ($url) {
                            $this->responses[$url] = $success;
                        })
                        ->catch(function (ExceptionInterface $err) use ($engine, $url) {
                            Logger::log(
                                sprintf(
                                    'Error submitting sitemap %s to %s. Exception: %s',
                                    $url,
                                    $engine,
                                    $err->getMessage()
                                ),
                                'warning'
                            );
                            $this->responses[$url] = false;
                        })
                        ->timeout(function () use ($engine, $url) {
                            Logger::log(
                                sprintf(
                                    'Error submitting sitemap %s to %s - timeout reached.',
                                    $url,
                                    $engine,
                                ),
                                'warning'
                            );
                            $this->responses[$url] = false;
                        });
                }
            }
        }
        $this->pool->wait();
    }

    /**
     * Notify search engine about single sitemap.
     *
     * @param string $enginePing Search engine ping URL. Ex. 'https://www.gooogle.com/ping?sitemap={sitemap_url}'.
     * @param string $sitemap URL of sitemap to submit.
     *
     * @return bool True if sitemap was submitted.
     *
     * @throws ExceptionInterface On submit error.
     */
    public static function pingSitemap(string $enginePing, string $sitemap): bool
    {
        (new CurlHttpClient())->request('GET', $enginePing . $sitemap);
        Logger::log(
            sprintf('Successfully submitted sitemap %s to %s', $sitemap, $enginePing),
            'success'
        );
        return true;
    }
}
