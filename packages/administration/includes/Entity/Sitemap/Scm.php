<?php

declare(strict_types=1);

namespace Scm\Entity\Sitemap;

use Cra\BlemmyaePpworks\Ppworks;
use Cra\BlemmyaePpworks\PpworksEpisodeCT;
use Cra\BlemmyaePpworks\PpworksSegmentCT;
use Cra\CtEditorial\EditorialCT;
use Cra\CtLanding\LandingCT;
use Cra\CtLearning\LearningCT;
use Cra\CtPeople\PeopleCT;
use Cra\CtWhitepaper\WhitepaperCT;
use Scm\Tools\Logger;
use Scm\Tools\Utils;
use WP_Query;

/**
 * Generate SCMagazine sitemap.
 *
 * Class Sitemap
 *
 * @package Scm\Sitemap
 */
class Scm extends AbstractSitemap
{
    /**
     * {@inheritDoc}
     */
    protected function isPathShouldBeSkippedForPostType(
        string $type,
        string $postPath,
        string $postId = ''
    ): bool {
        // Editorial Service posts are only used to be displayed on service landing pages.
        $isServiceEditorial = str_starts_with($postPath, '/service/') && 'editorial' === $type;

        return parent::isPathShouldBeSkippedForPostType(
            $type,
            $postPath,
            $postId
        ) || $isServiceEditorial;
    }

    /**
     * {@inheritDoc}
     */
    protected function getPostsExcludedFromSitemap(): array
    {
        return [214689, 472967];
    }

    /**
     * {@inheritdoc }
     */
    protected function isTermShouldBeSkipped(\WP_Term $term): bool
    {
        // Exclude by user input in the term edit page.
        if (get_field('field_6685708c5cd0f', $term->taxonomy . '_' . $term->term_id)) {
            return true;
        }

        // We do not need to add term to sitemap, if it has sponsor page args.
        // Sponsor page = landing with same topic and sponsor landing_type.
        if ($term->taxonomy === 'topic') {
            // Sponsor term exclude.
            $sponsorTerm = get_term_by('slug', 'sponsor', 'landing_type');

            $args = [
                'numberposts' => 1,
                'post_type' => LandingCT::POST_TYPE,
                'meta_query' => [
                    'relation' => 'AND',
                    [
                        'key' => 'type',
                        'value' => $sponsorTerm->term_id,
                    ],
                    [
                        // Multiple value field should be resolved via LIKE.
                        // Also, remember to put the ID inside parentheses (“”) otherwise you will
                        // match “12” with “123”.
                        'key' => 'topic',
                        'value' => '"' . $term->term_id . '"',
                        'compare' => 'LIKE',
                    ],
                ],
            ];

            $query = new WP_Query($args);

            return $query->have_posts();
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    protected function submit(): void
    {
        // Submit sitemaps on production environment only.
        if (!Utils::isProd()) {
            return;
        }
        parent::submit();
        // Submit Google News sitemap
        Logger::log('Submit for google news (including index) - Start', 'info');
        $newsUrls = [];
        $newsUrls['index'] = sprintf(
            '%s/%s',
            $this->siteUrl,
            $this->newsGenerator->getIndexFileName()
        );
        $newsUrls['sitemaps'] = array_map(
            fn(string $location) => str_replace($this->outputDir, $this->siteUrl, $location),
            $this->newsGenerator->getOutputFiles()
        );
        $this->submitter->submitSitemaps($newsUrls);
        Logger::log('Submit for google news (including index) - End', 'info');
    }

    /**
     * {@inheritDoc}
     */
    protected function getPostTypes(): array
    {
        return [
            EditorialCT::POST_TYPE,
            LandingCT::POST_TYPE,
            WhitepaperCT::POST_TYPE,
            LearningCT::POST_TYPE,
            PeopleCT::POST_TYPE,
            PpworksEpisodeCT::POST_TYPE,
            PpworksSegmentCT::POST_TYPE,
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function getTaxonomies(): array
    {
        return [LearningCT::TAXONOMY__TOPIC, Ppworks::TAXONOMY__SHOW];
    }

    /**
     * {@inheritDoc}
     */
    protected function getGoogleNewsPostTypes(): array
    {
        return [EditorialCT::POST_TYPE];
    }
}
