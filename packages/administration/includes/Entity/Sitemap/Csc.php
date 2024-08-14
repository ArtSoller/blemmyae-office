<?php

declare(strict_types=1);

namespace Scm\Entity\Sitemap;

use Cra\CtLanding\LandingCT;

/**
 * Generate Csc sitemap.
 *
 * Class Csc
 *
 * @package Scm\Sitemap
 */
class Csc extends AbstractSitemap
{
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
        return [];
    }

    /**
     * {@inheritDoc}
     */
    protected function getGoogleNewsPostTypes(): array
    {
        return [];
    }
}
