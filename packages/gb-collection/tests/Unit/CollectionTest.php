<?php

/**
 * Unit tests for Collection
 *
 * @package      Cra\GbCollection\Tests\Unit
 * @author       Gary Jones
 * @copyright    2021 CRA
 * @license      proprietary
 */

declare(strict_types=1);

namespace Cra\GbCollection\Tests\Unit;

use Cra\GbCollection\Collection as Testee;
use Cra\GbCollection\Tests\TestCase;

/**
 * Collection test case.
 */
class CollectionTest extends TestCase
{
    /**
     * A single example test.
     */
    public function testSample(): void
    {
        // Replace this with some actual testing code.
        static::assertTrue(( new Testee() )->isTrue());
    }

    /**
     * A single example test.
     */
    public function testCollection(): void
    {
        // Replace this with some actual testing code.
        static::assertFalse(false);
    }

    /**
     * A single example test.
     */
    public function testBar(): void
    {
        // Replace this with some actual testing code.
        static::assertEquals('Collection::bar()', ( new Testee() )->bar());
    }
}
