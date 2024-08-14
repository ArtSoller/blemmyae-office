<?php

/**
 * Integration tests for Foo
 *
 * @package      Cra\BlemmyaeAds\Tests\Integration
 * @author       Gary Jones
 * @copyright    2021 CRA
 * @license      proprietary
 */

declare(strict_types=1);

namespace Cra\BlemmyaeAds\Tests\Integration;

use Cra\BlemmyaeAds\Foo as Testee;
use WP_UnitTestCase;

/**
 * Foo test case.
 */
class FooTest extends WP_UnitTestCase
{
    /**
     * A single example test.
     */
    public function testFoo(): void
    {
        // Replace this with some actual integration testing code.
        static::assertTrue((new Testee())->isTrue());
    }
}
