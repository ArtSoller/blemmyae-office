<?php

/**
 * BlemmyaeBlocks class, creates an instance of NativeAdsManager
 * to allow dfp native ads injection
 *
 * @package   Cra\BlemmyaeBlocks
 * @author    Squiz Pty Ltd <products@squiz.net>
 * @copyright 2021 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   proprietary
 */

declare(strict_types=1);

namespace Cra\BlemmyaeBlocks;

use Cra\BlemmyaeBlocks\Block\BlockFactory;
use Cra\BlemmyaeBlocks\Endpoints\CtEditorial\EditorialWithRelatedBlock;
use Cra\BlemmyaeBlocks\Endpoints\CtLanding\ContentTeaserPagination;
use Cra\BlemmyaeBlocks\Endpoints\CtLanding\LandingByQueryResolver;
use Exception;

class BlemmyaeBlocks
{
    /**
     * @throws Exception
     */
    public function __construct()
    {
        $blockFactory = new BlockFactory();
        new NativeAdsManager(
            [
                NativeAdsManager::$nodesSupportingDfpNativesInjection["landingBySlug"] => new LandingByQueryResolver(
                    $blockFactory
                ),
                // phpcs:ignore
                NativeAdsManager::$nodesSupportingDfpNativesInjection["contentTeaserPosts"] => new ContentTeaserPagination(
                    $blockFactory
                ),
                // phpcs:ignore
                NativeAdsManager::$nodesSupportingDfpNativesInjection["editorialWithRelatedBlock"] => new EditorialWithRelatedBlock(
                    $blockFactory
                ),
            ]
        );
    }
}
