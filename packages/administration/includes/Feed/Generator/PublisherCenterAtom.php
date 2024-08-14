<?php

/**
 * @author  Konstantin Gusev <guvkon.net@icloud.com>
 * @license proprietary
 */

declare(strict_types=1);

namespace Scm\Feed\Generator;

use Cra\BlemmyaeApplications\Entity\Permalink;
use Cra\BlemmyaePpworks\PpworksSegmentCT;
use Cra\CtEditorial\EditorialCT;
use DateInterval;
use Scm\Feed\Entity\Post;
use Scm\Feed\Setup;
use WP_Query;
use WP_Term;
use XMLWriter;

/**
 * Class for generating Atom feeds.
 *
 * Generated Atom feeds follow Google Publisher Center technical requirements.
 */
class PublisherCenterAtom implements GeneratorInterface
{
    /**
     * @var array|Post[]
     */
    private array $posts;

    private XMLWriter $writer;

    /**
     * Generates Atom feed for the specified term.
     *
     * @param WP_Term $term
     * @param string $app
     * @param int $daysOld Filter out posts older than this value in days.
     *
     * @return string
     */
    public function generateFeedForTerm(WP_Term $term, string $app, int $daysOld = 7): string
    {
        $this->loadPostsForTerm($term, $app, $daysOld);

        $this->writeFeedHeader();
        $this->writeFeedInformation($term, $app);

        foreach ($this->posts as $post) {
            $this->writeEntry($post, $app);
        }

        return $this->finishWriting();
    }

    /**
     * Load posts for a term.
     *
     * @param WP_Term $term
     * @param string $app
     * @param int $daysOld Filter out posts older than this value in days.
     *
     * @return void
     */
    private function loadPostsForTerm(WP_Term $term, string $app, int $daysOld): void
    {
        $minimumSuggestedPostsCount = 10;
        $after = date_create('now', wp_timezone());
        $after->sub(DateInterval::createFromDateString("$daysOld day"));
        // @todo: Optimize.
        $include = new WP_Query([
            'post_type' => ['editorial', 'ppworks_segment'],
            'post_status' => 'publish',
            'nopaging' => true,
            'date_query' => [
                'after' => $after->format('c'),
            ],
            'fields' => 'ids',
            'tax_query' => [
                [
                    'taxonomy' => 'applications',
                    'terms' => $app,
                    'field' => 'slug',
                ]
            ],
        ]);
        // Dirty way to populate empty feeds, to provide a fallback.
        $queryAdditionalArgs = $include->have_posts() ? [
            'post__in' => $include->posts,
            'nopaging' => true,
        ] : [
            'nopaging' => false,
            'posts_per_page' => (string)$minimumSuggestedPostsCount,
        ];
        $query = new WP_Query(
            array_merge([
                'post_type' => ['editorial', 'ppworks_segment'],
                'post_status' => 'publish',
                'tax_query' => [
                    [
                        'taxonomy' => $term->taxonomy,
                        'terms' => [$term->slug],
                        'field' => 'slug',
                        'include_children' => true,
                        'operator' => 'IN',
                    ],
                ],
                'orderby' => 'publish_date',
                'order' => 'DESC',
            ], $queryAdditionalArgs)
        );
        // Dirty way to populate small feeds, to provide a fallback.
        $fallbackCount = $minimumSuggestedPostsCount - count($query->posts);
        if ($fallbackCount > 0) {
            $fallback = new WP_Query([
                'post_type' => ['editorial', 'ppworks_segment'],
                'post__not_in' => array_column($query->posts, 'ID'),
                'post_status' => 'publish',
                'nopaging' => false,
                'posts_per_page' => (string)$fallbackCount,
                'orderby' => 'publish_date',
                'order' => 'DESC',
                'tax_query' => [
                    'relation' => 'AND',
                    [
                        'taxonomy' => 'applications',
                        'terms' => $app,
                        'field' => 'slug',
                    ]
                ]
            ]);
            // Remove tax_query from the 'Latest' feed.
            if ($term->slug !== 'latest') {
                $fallback->tax_query->queries[] = [
                    'taxonomy' => $term->taxonomy,
                    'terms' => [$term->slug],
                    'field' => 'slug',
                    'include_children' => true,
                    'operator' => 'IN',
                ];
            }

            if ($fallback->have_posts()) {
                $temp = $query;
                $query = new WP_Query();
                $query->posts = array_merge($temp->posts, $fallback->posts);
                $query->post_count = $temp->post_count + $fallback->post_count;
            }
        }
        $this->posts = [];
        while ($query->have_posts()) {
            $this->posts[] = new Post($query->next_post());
        }
    }

    /**
     * Write Atom feed header.
     *
     * @return void
     */
    private function writeFeedHeader(): void
    {
        $this->writer = new XMLWriter();
        $this->writer->openMemory();
        $this->writer->startDocument('1.0', 'UTF-8');

        $this->writer->setIndent(true);

        $this->writer->startElement('feed');
        $this->writer->writeAttribute('xmlns', 'http://www.w3.org/2005/Atom');
        $this->writer->writeAttribute('xmlns:media', 'http://search.yahoo.com/mrss/');
    }

    /**
     * Write Atom feed information.
     *
     * @param WP_Term $term
     * @param string $app
     *
     * @return void
     */
    private function writeFeedInformation(WP_Term $term, string $app): void
    {
        /* == Required feed elements. == */

        $termLink = Permalink::buildTermPermalink($term, $app);
        $this->writer->writeElement('id', $termLink);
        $this->writer->writeElement(
            'title',
            strtoupper($app) . ' feed for ' . ucwords($term->name)
        );
        $this->writer->writeElement('updated', date('c'));

        /* == Recommended feed elements. == */

        $this->writer->startElement('link');
        $this->writer->writeAttribute('href', $termLink);
        $this->writer->writeAttribute('rel', 'alternate');
        $this->writer->writeAttribute('type', 'text/html');
        $this->writer->endElement();

        $this->writer->startElement('link');
        // This is the feed link format set on Cerberus through reverse proxy.
        $this->writer->writeAttribute('href', Setup::frontendFeedPath($term));
        $this->writer->writeAttribute('rel', 'self');
        $this->writer->writeAttribute('type', 'application/atom+xml');
        $this->writer->endElement();

        $this->writer->startElement('link');
        $this->writer->writeAttribute('href', Setup::PUBSUBHUBBUB_HUB_URL);
        $this->writer->writeAttribute('rel', 'hub');
        $this->writer->endElement();

        /* == Optional feed elements. == */

        $this->writer->startElement('category');
        $this->writer->writeAttribute('term', $term->slug);
        $this->writer->writeAttribute('label', ucwords($term->name));
        $this->writer->endElement();

        $this->writer->writeElement(
            'logo',
            "https://files.scmagazine.com/logo/$app-horizontal-white-with-resource.png"
        );

        $this->writer->startElement('rights');
        $this->writer->writeAttribute('type', 'html');
        $year = date_create()->format('Y');
        $this->writer->text("Copyright &copy; $year CyberRisk Alliance, LLC All Rights Reserved");
        $this->writer->endElement();
    }

    /**
     * Write entry.
     *
     * @param Post $post_wrapper
     * @param string $app
     *
     * @return void
     */
    private function writeEntry(Post $post_wrapper, string $app): void
    {
        $post = $post_wrapper->post;

        $this->writer->startElement('entry');

        /* == Required entry elements == */

        $this->writer->startElement('id');
        $this->writer->writeAttribute('isPermaLink', 'false');
        $this->writer->text($post_wrapper->guid($app));
        $this->writer->endElement();

        $this->writer->writeElement('title', $this->stripTags($post->post_title));
        $this->writer->writeElement(
            'updated',
            get_post_modified_time('c', false, $post)
        );

        /* == Recommended entry elements == */

        foreach ($post_wrapper->authors($app) as $author) {
            $this->writer->startElement('author');
            $this->writer->writeElement('name', $author['name']);
            $this->writer->writeElement('uri', $author['uri']);
            $this->writer->endElement();
        }

        $this->writer->startElement('summary');
        $this->writer->writeAttribute('type', 'html');
        $this->writer->text(wp_kses_post($post_wrapper->deck()));
        $this->writer->endElement();

        $this->writer->startElement('link');
        $this->writer->writeAttribute('rel', 'alternate');
        $this->writer->writeAttribute('href', Permalink::buildPostPermalink($post->ID));
        $this->writer->writeAttribute('type', 'text/html');
        $this->writer->endElement();

        /* == Optional entry elements == */

        foreach ($post_wrapper->topics() as $topic) {
            $this->writer->startElement('category');
            $this->writer->writeAttribute('term', $topic->slug);
            $this->writer->writeAttribute('label', ucwords($topic->name));
            $this->writer->endElement();
        }

        $this->writer->writeElement(
            'published',
            get_post_time('c', false, $post)
        );

        /* == Google Publisher Center entry elements == */

        switch ($post->post_type) {
            case 'ppworks_segment':
                $imageField = PpworksSegmentCT::GROUP_PPWORKS_PODCAST_BASIC__FIELD_FEATURED_IMAGE;
                $imageCaptionField = null;
                break;
            case 'editorial':
            default:
                $imageField = EditorialCT::GROUP_EDITORIAL_ADVANCED__FIELD_FEATURED_IMAGE;
                $imageCaptionField = EditorialCT::GROUP_EDITORIAL_ADVANCED__FIELD_FEATURED_IMAGE_CAPTION;
                break;
        }

        $image = $post_wrapper->featuredImage($imageField, $imageCaptionField);
        if ($image) {
            $this->writer->startElement('media:content');
            $this->writer->writeAttribute('url', $image['url']);
            $this->writer->writeAttribute('type', $image['mime_type']);
            $this->writer->writeAttribute('medium', 'image');
            $this->writer->writeAttribute('height', (string)$image['height']);
            $this->writer->writeAttribute('width', (string)$image['width']);
            if (!empty($image['caption'])) {
                $this->writer->startElement('media:description');
                $this->writer->writeAttribute('type', 'html');
                $this->writer->text($image['caption']);
                $this->writer->endElement();
            }
            $this->writer->endElement();
        }

        $this->writer->endElement(); // end "entry" tag
    }

    /**
     * Finish writing and output the resulting XML as string.
     *
     * @return string
     */
    private function finishWriting(): string
    {
        $this->writer->endElement(); // end "feed" tag
        $this->writer->endDocument();

        return $this->writer->outputMemory();
    }

    /**
     * Strip HTML tags from the string while keeping HTML entities intact as UTF-8 characters.
     *
     * @param string $string
     *
     * @return string
     */
    private function stripTags(string $string): string
    {
        return wp_strip_all_tags(html_entity_decode($string));
    }
}
