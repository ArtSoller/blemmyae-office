<?php

/**
 * Integration tests for BlemmyaeApplications
 *
 * @package      Cra\BlemmyaeApplications\Tests\Integration
 * @author       Gary Jones
 * @copyright    2021 CRA
 * @license      proprietary
 */

declare(strict_types=1);

namespace Cra\BlemmyaeApplications\Tests\Integration;

use Cra\BlemmyaeApplications\BlemmyaeApplications as Testee;
use WP_UnitTestCase;

/**
 * BlemmyaeApplications test case.
 */
class BlemmyaeApplicationsTest extends WP_UnitTestCase
{
    /**
     * A single example test.
     */
    public function testBlemmyaeApplications(): void
    {
        // Replace this with some actual integration testing code.
        static::assertTrue((new Testee())->isTrue());
    }
}
