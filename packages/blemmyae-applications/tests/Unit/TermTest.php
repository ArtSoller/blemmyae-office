<?php

/**
 * Unit tests for Term
 *
 * @package      Cra\BlemmyaeApplications\Tests\Unit
 * @author       Eugene Yakovenko
 * @copyright    2023 CRA
 * @license      proprietary
 */

declare(strict_types=1);

namespace Cra\BlemmyaeApplications\Tests\Unit;

use Cra\BlemmyaeApplications\Entity\Term as Testee;
use Cra\BlemmyaeApplications\Tests\TestCase;

/**
 * Term test case.
 */
class TermTest extends TestCase
{
    /**
     * Check if SCM term exists.
     */
    public function testGetAppTermBy(): void
    {
        require_once(__DIR__ . '/../../../../wp-load.php');

        $term = Testee::getAppTermBy('slug', 'scm');
        $this->assertSame($term->term_id, 74238);
    }

    /**
     * Check if Ciso term exists.
     */
    public function testGetAppTermIdByAppSlug(): void
    {
        require_once(__DIR__ . '/../../../../wp-load.php');

        $termId = Testee::getAppTermIdByAppSlug('ciso');
        $this->assertSame($termId, 74239);
    }

    /**
     * Check if Csc term exists.
     */
    public function testGetAppTermByPostId(): void
    {
        require_once(__DIR__ . '/../../../../wp-load.php');

        // CSC homepage id
        $termId = Testee::getAppTermByPostId(443916);
        $this->assertSame($termId->term_id, 74240);
    }
}
