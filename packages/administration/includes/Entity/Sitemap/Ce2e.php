<?php

declare(strict_types=1);

namespace Scm\Entity\Sitemap;

use Cra\CtEditorial\EditorialCT;
use Cra\CtLanding\LandingCT;

/**
 * Generate Ce2e sitemap.
 *
 * Class Ce2e
 *
 * @package Scm\Sitemap
 */
class Ce2e extends AbstractSitemap
{
    /**
     * {@inheritDoc}
     */
    protected function getPostTypes(): array
    {
        return [LandingCT::POST_TYPE, EditorialCT::POST_TYPE];
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
