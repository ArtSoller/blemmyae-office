<?php

/** @noinspection PhpIllegalPsrClassPathInspection */

declare(strict_types=1);

namespace Scm\Entity\Sitemap;

use DateInterval;
use DateTime;
use Exception;
use Generator;
use Icamys\SitemapGenerator\SitemapGenerator;
use WP_Post;
use WP_Query;

use function get_post_permalink;

/**
 * Generate sitemap.
 *
 * Class Sitemap
 *
 * @package Scm\Sitemap
 */
class Sitemap
{
    private string $siteUrl;

    private string $outputDir;

    private string $publication;

    private ?SitemapGenerator $generator;

    private ?GoogleNewsSitemapGenerator $newsGenerator;

    /**
     * Sitemap constructor.
     *
     * @param string $url Site URL to use as host for all URLs.
     * @param string $directory Output directory to put generated sitemaps.
     * @param string $publication Name of the publication.
     */
    public function __construct(string $url, string $directory, string $publication)
    {
        $this->siteUrl = $url;
        $this->outputDir = $directory;
        $this->publication = $publication;
    }

    /**
     * Generate sitemap for the site.
     *
     * @param string[] $postTypes
     * @param string[] $taxonomies
     * @param string[] $newsPostTypes
     */
    public function generate(array $postTypes, array $taxonomies, array $newsPostTypes): void
    {
        set_time_limit(0);

        // Init sitemap generator.
        $this->generator = new SitemapGenerator($this->siteUrl, $this->outputDir);
        $this->generator->setMaxUrlsPerSitemap(1000);
        $this->generator->setSitemapIndexFileName("sitemap-index.xml");
        $this->generator->setSitemapFileName("sitemap.xml");

        foreach ($postTypes as $postType) {
            $this->generateForPostType($postType);
        }
        foreach ($taxonomies as $taxonomy) {
            $this->generateForTaxonomy($taxonomy);
        }

        // Init Google News sitemap generator.
        $this->newsGenerator = new GoogleNewsSitemapGenerator(
            $this->siteUrl,
            $this->outputDir,
            $this->publication
        );
        $this->newsGenerator
            ->setMaxUrlsPerSitemap(1000)
            ->setSitemapIndexFileName('sitemap-news-index.xml')
            ->setSitemapFileName('sitemap-news.xml');

        foreach ($newsPostTypes as $postType) {
            $this->generateForGoogleNews($postType);
        }

        // Remove previously generated sitemaps.
        array_map('unlink', glob("$this->outputDir/sitemap*.xml"));
        // Write just generated sitemaps to the file system.
        $this->generator->finalize();
        $this->newsGenerator->finalize();
        // Remove temporarily files created during sitemap generation.
        array_map('unlink', glob("$this->outputDir/sm-*.xml"));
    }

    /**
     * Generate sitemap for post type.
     *
     * @param string $type
     */
    private function generateForPostType(string $type): void
    {
        foreach ($this->getPostsOfType($type) as $post) {
            try {
                $postPath = parse_url(get_post_permalink($post), PHP_URL_PATH);
                if (!$postPath) {
                    throw new Exception("Empty path for post $post->ID of type $type");
                }
                if ($this->isPathShouldBeSkippedForPostType($type, $postPath)) {
                    continue;
                }
                $this->generator->addURL(
                    $postPath,
                    new DateTime(
                        'landing' === $type ? 'now' :
                            $post->post_modified
                    )
                );
            } catch (Exception $exception) {
                error_log($exception->getMessage());
            }
        }
        $this->generator->flush();
    }

    /**
     * Get all posts of type.
     *
     * @param string $type
     *
     * @return WP_Post[]|Generator
     */
    private function getPostsOfType(string $type): Generator
    {
        $query = new WP_Query(
            [
                'post_type' => $type,
                'post_status' => ['publish'],
                'nopaging' => true,
            ]
        );
        while ($query->have_posts()) {
            yield $query->post;
            $query->next_post();
        }
    }

    /**
     * Checks if link should be skipped from sitemap.
     *
     * @param string $type
     * @param string $postPath
     *
     * @return bool
     */
    private function isPathShouldBeSkippedForPostType(string $type, string $postPath): bool
    {
        // Posts with missing editorial type, learning type, etc.
        if (0 === strpos($postPath, '/%')) {
            return true;
        }

        // Editorial Service posts are only used to be displayed on service landing pages.
        if (0 === strpos($postPath, '/service/') && $type === 'editorial') {
            return true;
        }

        return false;
    }

    /**
     * Generates sitemap for taxonomy.
     *
     * @param string $taxonomy
     */
    private function generateForTaxonomy(string $taxonomy): void
    {
        foreach (get_terms($taxonomy) as $term) {
            try {
                $postPath = parse_url(get_term_link($term), PHP_URL_PATH);
                if (!$postPath) {
                    throw new Exception("Empty path for term $term->term_id on $term->taxonomy");
                }
                $this->generator->addURL($postPath, new DateTime('now'));
            } catch (Exception $exception) {
                error_log($exception->getMessage());
            }
        }
        $this->generator->flush();
    }

    /**
     * Generates Google News sitemap for post types.
     *
     * @param string $type
     */
    private function generateForGoogleNews(string $type): void
    {
        foreach ($this->getPostsOfTypeForGoogleNews($type) as $post) {
            try {
                $postPath = parse_url(get_post_permalink($post), PHP_URL_PATH);
                if (!$postPath) {
                    throw new Exception("Empty path for post $post->ID of type $type");
                }
                if ($this->isPathShouldBeSkippedForPostType($type, $postPath)) {
                    continue;
                }
                $this->newsGenerator->addURL(
                    $postPath,
                    new DateTime($post->post_modified),
                    $post->post_title
                );
            } catch (Exception $exception) {
                error_log($exception->getMessage());
            }
        }
        $this->newsGenerator->flush();
    }

    /**
     * Get all posts of type.
     *
     * @param string $type
     *
     * @return WP_Post[]|Generator
     */
    private function getPostsOfTypeForGoogleNews(string $type): Generator
    {
        $after = (new DateTime())->sub(new DateInterval('P2D'));
        $query = new WP_Query(
            [
                'post_type' => $type,
                'post_status' => ['publish'],
                'nopaging' => true,
                'date_query' => [
                    'after' => $after->format('c'),
                ],
            ]
        );
        while ($query->have_posts()) {
            yield $query->post;
            $query->next_post();
        }
    }
}
