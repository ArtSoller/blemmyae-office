<?php

namespace Scm\Entity\Sitemap;

use Cra\BlemmyaeApplications\BlemmyaeApplications;
use Cra\BlemmyaeApplications\Entity\Permalink;
use DateInterval;
use DateTime;
use Exception;
use Generator;
use Icamys\SitemapGenerator\SitemapGenerator;
use Scm\Tools\Utils;
use WP_Query;
use WP_Term;

/**
 * Generate Abstract Sitemap.
 *
 * AbstractSitemap
 */
abstract class AbstractSitemap
{
    protected string $siteUrl;

    protected string $outputDir;
    protected ?SitemapGenerator $generator;
    protected ?GoogleNewsSitemapGenerator $newsGenerator;
    protected Submitter $submitter;
    private string $publication;
    private string $application;

    /**
     * Sitemap constructor.
     *
     * @param string $url Site URL to use as host for all URLs.
     * @param string $directory Output directory to put generated sitemaps.
     * @param string $publication Name of the publication.
     */
    public function __construct(
        string $url,
        string $directory,
        string $publication,
        string $application
    ) {
        $this->siteUrl = $url;
        $this->outputDir = $directory;
        $this->generator = $this->initGenerator();
        $this->submitter = new Submitter();
        $this->publication = $publication;
        $this->application = $application;
    }

    /**
     * Initialize sitemap generator.
     *
     * @param int $maxUrlsPerSitemap
     * @param string $sitemapIndexFileName
     * @param string $sitemapFileName
     *
     * @return SitemapGenerator
     */
    private function initGenerator(
        int $maxUrlsPerSitemap = 1000,
        string $sitemapIndexFileName = 'sitemap-index.xml',
        string $sitemapFileName = 'sitemap.xml'
    ): SitemapGenerator {
        return (new SitemapGenerator($this->siteUrl, $this->outputDir))
            ->setMaxUrlsPerSitemap($maxUrlsPerSitemap)
            ->setSitemapIndexFileName($sitemapIndexFileName)
            ->setSitemapFileName($sitemapFileName);
    }

    /**
     * Generate sitemap for the site.
     */
    public function generate(): void
    {
        set_time_limit(0);

        $postTypes = $this->getPostTypes();
        $taxonomies = $this->getTaxonomies();
        $newsPostTypes = $this->getGoogleNewsPostTypes();

        if ($newsPostTypes) {
            // Generate sitemap for Google News first.
            $this->newsGenerator = $this->initGoogleNewsGenerator();
            array_map(
                fn(string $postType) => $this->generateForGoogleNews($postType),
                $newsPostTypes
            );
        }

        array_map(fn(string $postType) => $this->generateForPostType($postType), $postTypes);
        array_map(fn(string $taxonomy) => $this->generateForTaxonomy($taxonomy), $taxonomies);

        $this->finalizeGeneration($newsPostTypes);
    }

    /**
     * Initialize Google News sitemap generator.
     *
     * @param int $maxUrlsPerSitemap
     * @param string $sitemapIndexFileName
     * @param string $sitemapFileName
     *
     * @return GoogleNewsSitemapGenerator
     */
    private function initGoogleNewsGenerator(
        int $maxUrlsPerSitemap = 1000,
        string $sitemapIndexFileName = 'sitemap-news-index.xml',
        string $sitemapFileName = 'sitemap-news.xml'
    ): GoogleNewsSitemapGenerator {
        return (new GoogleNewsSitemapGenerator(
            $this->siteUrl,
            $this->outputDir,
            $this->publication
        ))
            ->setMaxUrlsPerSitemap($maxUrlsPerSitemap)
            ->setSitemapIndexFileName($sitemapIndexFileName)
            ->setSitemapFileName($sitemapFileName);
    }

    /**
     * Generate Google News sitemap for post types.
     *
     * @param string $type
     */
    private function generateForGoogleNews(string $type): void
    {
        $posts = $this->getPostsOfTypeForGoogleNews($type, 10);
        while ($posts->valid()) {
            try {
                $postId = $posts->current();

                // Parse URL and remove `/` from the end.
                $postPath = parse_url(get_post_permalink($postId), PHP_URL_PATH);
                $postPath = rtrim($postPath, '/');

                if (!$postPath) {
                    throw new Exception("Empty path for post $postId of type $type");
                }
                if ($this->isPathShouldBeSkippedForPostType($type, $postPath, $postId)) {
                    $posts->send(true);
                    continue;
                }

                $this->newsGenerator->addURL(
                    $postPath,
                    new DateTime(get_post_modified_time('Y-m-d', true, $postId, true)),
                    get_the_title($postId)
                );
            } catch (Exception $exception) {
                Logger::log(
                    sprintf(
                        ' Error adding URL to sitemap. Exception: %s',
                        $exception->getMessage()
                    ),
                    'error'
                );
            }
            $posts->send(false);
        }
        $this->newsGenerator->flush();
    }

    /**
     * Get all posts of type for Google News.
     *
     * @param string $type
     * @param int $minimalPostCount
     *
     * @return Generator
     */
    private function getPostsOfTypeForGoogleNews(string $type, int $minimalPostCount): Generator
    {
        $newsDateBoundary = (new DateTime())->sub(new DateInterval('P2D'));
        $query = new WP_Query(
            [
                'post_type' => $type,
                'post_status' => ['publish'],
                'nopaging' => true,
                'date_query' => [
                    'after' => $newsDateBoundary->format('c'),
                ],
                'fields' => 'ids',
            ]
        );
        $fallbackCount = $minimalPostCount - $query->post_count;
        $postsSkipped = 0;
        foreach ($query->posts as $postId) {
            if (yield $postId) {
                $postsSkipped++;
            }
        }
        $fallbackCount += $postsSkipped;

        while ($fallbackCount > 0) {
            $excludeIds = $query->posts;
            $postsSkipped = 0;
            $query = new WP_Query([
                'post_type' => $type,
                'post_status' => ['publish'],
                'nopaging' => false,
                'posts_per_page' => $fallbackCount,
                'fields' => 'ids',
                'post__not_in' => $excludeIds,
            ]);
            foreach ($query->posts as $postId) {
                if (yield $postId) {
                    $postsSkipped++;
                }
            }
            $fallbackCount = $postsSkipped;
        }
    }

    /**
     * Check if link should be skipped from sitemap.
     *
     * @param string $type
     * @param string $postPath
     * @param string $postId
     *
     * @return bool
     */
    protected function isPathShouldBeSkippedForPostType(
        string $type,
        string $postPath,
        string $postId = ''
    ): bool {
        // Posts with missing editorial type, learning type, etc.
        return str_starts_with($postPath, '/%');
    }

    /**
     * Get post types to include in sitemap.
     *
     * @return array
     */
    abstract protected function getPostTypes(): array;

    /**
     * Taxonomies to include in sitemap.
     *
     * @return array
     */
    abstract protected function getTaxonomies(): array;

    /**
     * Get post types to include in Google News sitemap.
     *
     * @return array
     */
    abstract protected function getGoogleNewsPostTypes(): array;

    /**
     * Generate sitemap for specific post type.
     *
     * @param string $type
     */
    protected function generateForPostType(string $type): void
    {
        foreach ($this->getPostsOfType($type) as $postId) {
            try {
                // Parse URL and remove `/` from the end.
                $postPath = parse_url(get_post_permalink($postId), PHP_URL_PATH);
                $postPath = rtrim($postPath, '/');

                if (!$postPath) {
                    throw new Exception("Empty path for post $postId of type $type");
                }
                if ($this->isPathShouldBeSkippedForPostType($type, $postPath, $postId)) {
                    continue;
                }

                // If path is not scm => we need to remove apps prefix.
                // This code should be after skip, because we will remove apps prefix.
                if (
                    $type === 'landing'
                    && !BlemmyaeApplications::isAppsLandingPath(
                        BlemmyaeApplications::SCM,
                        $postPath,
                        $type
                    )
                ) {
                    $postPath = Permalink::removeAppsPrefixFromPath($postPath);
                }
                $this->generator->addURL(
                    'landing' === $type && $postPath === '/homepage' ? '/' : $postPath,
                    new DateTime(
                        'landing' === $type ? 'now' :
                            get_post_modified_time('Y-m-d', true, $postId, true)
                    )
                );
            } catch (Exception $exception) {
                Logger::log(
                    sprintf(
                        ' Error adding URL to sitemap. Exception: %s',
                        $exception->getMessage()
                    ),
                    'warning'
                );
            }
        }
        $this->generator->flush();
    }

    /**
     * Get all posts of type IDs.
     *
     * @param string $type
     *
     * @return Generator
     */
    protected function getPostsOfType(string $type): Generator
    {
        $defaultArgs = [
            'post_type' => $type,
            'post_status' => ['publish'],
            'nopaging' => true,
            'fields' => 'ids',
            'post__not_in' => $this->getPostsExcludedFromSitemap(),
        ];

        $taxQuery = $type !== 'people' ? [
            'tax_query' => [
                [
                    'taxonomy' => BlemmyaeApplications::TAXONOMY,
                    'terms' => $this->application,
                    'field' => 'slug',
                ]
            ]
        ] : [];

        $args = array_merge($defaultArgs, $taxQuery);
        $query = new WP_Query($args);
        $countAll = count($query->posts);
        $throttle = 1000;
        $throttleCount = $count = 0;

        Logger::log(Utils::memUsage(), 'debug');
        Logger::log("Processed posts - $countAll/$count", 'info');

        foreach ($query->posts as $postId) {
            yield $postId;
            if ($throttleCount > $throttle) {
                Logger::log(Utils::memUsage(), 'debug');
                $throttleCount = 0;
                $count += $throttle;
                Logger::log("Processed posts - $countAll/$count", 'info');
            }
            $throttleCount++;
        }

        Logger::log(Utils::memUsage(), 'debug');
        Logger::log("Processed posts - $countAll/$countAll", 'info');

        unset($query);
    }

    /**
     * Generate sitemap for specific taxonomy.
     *
     * @param string $taxonomy
     */
    protected function generateForTaxonomy(string $taxonomy): void
    {
        $terms = get_terms($taxonomy, ['hide_empty' => $this->hideEmptyTerms($taxonomy)]);
        foreach ($terms as $term) {
            try {
                if ($this->isTermShouldBeSkipped($term)) {
                    continue;
                }

                $postPath = parse_url(get_term_link($term), PHP_URL_PATH);
                if (!$postPath) {
                    throw new Exception("Empty path for term $term->term_id on $term->taxonomy");
                }
                $this->generator->addURL($postPath, new DateTime('now'));
            } catch (Exception $exception) {
                Logger::log(
                    sprintf(
                        ' Error adding URL to sitemap. Exception: %s',
                        $exception->getMessage()
                    ),
                    'warning'
                );
            }
        }
        $this->generator->flush();
    }

    /**
     * Get the value of the 'hide_empty' argument that should be passed to
     * get_terms in 'generateForTaxonomy'.
     *
     * @param string $taxonomy
     *
     * @return bool
     */
    protected function hideEmptyTerms(string $taxonomy): bool
    {
        return true;
    }

    /**
     * Check if topic should be skipped.
     *
     * @param WP_Term $term
     *
     * @return bool
     */
    protected function isTermShouldBeSkipped(WP_Term $term): bool
    {
        // You shouldn't skip anything by default.
        return false;
    }

    /**
     * Get array of psots excluded from sitemap.
     *
     * @return array
     *  Array of post's IDs excluded from sitemap.
     */
    protected function getPostsExcludedFromSitemap(): array
    {
        // @todo create editable options page for WP instead of hardcode.
        return [];
    }

    /**
     * Remove temporary files created during sitemap generation and previously
     * generated sitemaps as well. Write just generated sitemaps to the file
     * system and cleanup output directory.
     *
     * @param array $newsPostTypes
     *
     * @return void
     */
    protected function finalizeGeneration(array $newsPostTypes): void
    {
        // Remove previously generated sitemaps.
        array_map('unlink', glob("$this->outputDir/sitemap*.xml"));

        // If sitemap index does not exist => use sitemap-index as name.
        if (empty($this->generator->getGeneratedFiles()['sitemaps_index_location'])) {
            $this->generator->setSitemapFilename('sitemap-index.xml');
        }

        // Write just generated sitemaps to the file system.
        if ($newsPostTypes) {
            $this->newsGenerator?->finalize();
        }
        $this->generator->finalize();

        // Remove temporarily files created during sitemap generation.
        array_map('unlink', glob("$this->outputDir/sm-*.xml"));
        $this->submit();
    }

    /**
     * Submit newly generated sitemaps to search engines.
     *
     * @return void
     */
    protected function submit(): void
    {
        // Submit sitemaps on production environment only.
        if (!Utils::isProd()) {
            return;
        }
        Logger::log('Submit (including index) - Start', 'info');
        $sitemapFiles = $this->generator->getGeneratedFiles();
        $sitemapUrls = [];
        $sitemapUrls['index'] = $sitemapFiles['sitemaps_index_url'];
        $sitemapUrls['sitemaps'] = array_map(
            fn(string $location) => str_replace($this->outputDir, $this->siteUrl, $location),
            $sitemapFiles['sitemaps_location']
        );
        $this->submitter->submitSitemaps($sitemapUrls);
        Logger::log('Submit (including index) - End', 'info');
    }
}
