<?php

/**
 * Unit tests for Permalink
 *
 * @package      Cra\BlemmyaeApplications\Tests\Unit
 * @author       Eugene Yakovenko
 * @copyright    2023 CRA
 * @license      proprietary
 */

declare(strict_types=1);

namespace Cra\BlemmyaeApplications\Tests\Unit;

use Cra\BlemmyaeApplications\Entity\Permalink as Testee;
use Cra\BlemmyaeApplications\Tests\TestCase;

/**
 * Permalink test case.
 */
class PermalinkTest extends TestCase
{
    /**
     * Check that WP_SITEURL is replaced by FRONTEND_URI_SCM for frontend links.
     */
    public function testReplaceBasePathByApplicationSlug(): void
    {
        require_once(__DIR__ . '/../../../../wp-load.php');

        $link = Testee::replaceBasePathByApplicationSlug(WP_SITEURL, 'scm');
        $this->assertSame($link, FRONTEND_URI_SCM);
    }

    /**
     * Check that the correct frontend URL is returned by app slug.
     */
    public function testBuildFrontendPathByApp(): void
    {
        $link = Testee::buildFrontendPathByApp('ce2e');
        $this->assertSame($link, FRONTEND_URI_CE2E);
    }

    /**
     * Check that the correct frontend URL is returned by app slug.
     */
    public function testRemoveAppsPrefixFromSlug(): void
    {
        $link = Testee::removeAppsPrefixFromSlug('/_ce2e-test-url');
        $this->assertSame($link, '/test-url');
    }

    /**
     * Check that app prefix is removed from slug.
     * @todo research if we still use such logic
     */
    public function testRemoveAppsPrefixFromPath(): void
    {
        $link = Testee::removeAppsPrefixFromPath('https://www.scmagazine.com/_ce2e-test-url');
        $this->assertSame($link, 'https://www.scmagazine.com/test-url');
    }

    /**
     * Check that the WP_HOME is replaced by frontend URL.
     */
    public function testUpdateFrontendLinkForApps(): void
    {
        require_once(__DIR__ . '/../../../../wp-load.php');

        $link = Testee::updateFrontendLinkForApps('scm', WP_HOME . '/some-url');
        $this->assertSame($link, FRONTEND_URI_SCM . '/some-url');
    }
}
