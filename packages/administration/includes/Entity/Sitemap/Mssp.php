<?php

declare(strict_types=1);

namespace Scm\Entity\Sitemap;

use Cra\CtEditorial\EditorialCT;
use Cra\CtLanding\LandingCT;

/**
 * Generate Mssp sitemap.
 *
 * Class Mssp
 *
 * @package Scm\Sitemap
 */
class Mssp extends AbstractSitemap
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
