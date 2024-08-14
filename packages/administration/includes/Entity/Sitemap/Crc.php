<?php

declare(strict_types=1);

namespace Scm\Entity\Sitemap;

use Cra\CtLanding\LandingCT;
use Cra\CtLearning\LearningCT;

/**
 * Generate CRC sitemap.
 *
 * Class Crc
 *
 * @package Scm\Sitemap
 */
class Crc extends AbstractSitemap
{
    /**
     * {@inheritDoc}
     *
     * @todo rename/update - it's hard to understand what this function do.
     */
    protected function hideEmptyTerms($taxonomy): bool
    {
        // Empty Community Region terms must be listed in sitemap.
        return LearningCT::TAXONOMY__COMMUNITY_REGION !== $taxonomy;
    }

    /**
     * {@inheritDoc}
     */
    protected function getPostTypes(): array
    {
        return [LandingCT::POST_TYPE];
    }

    /**
     * {@inheritDoc}
     */
    protected function getTaxonomies(): array
    {
        return [LearningCT::TAXONOMY__COMMUNITY_REGION];
    }

    /**
     * {@inheritDoc}
     */
    protected function getGoogleNewsPostTypes(): array
    {
        return [];
    }
}
