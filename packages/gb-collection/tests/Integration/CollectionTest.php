<?php

/**
 * Integration tests for Collection
 *
 * @package      Cra\GbCollection\Tests\Integration
 * @author       Gary Jones
 * @copyright    2021 CRA
 * @license      proprietary
 */

declare(strict_types=1);

namespace Cra\GbCollection\Tests\Integration;

use Cra\GbCollection\Collection as Testee;
use WP_UnitTestCase;

/**
 * Collection test case.
 */
class CollectionTest extends WP_UnitTestCase
{
    /**
     * A single example test.
     */
    public function testCollection(): void
    {
        // Replace this with some actual integration testing code.
        static::assertTrue(( new Testee() )->isTrue());
    }
}
