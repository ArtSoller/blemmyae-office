<?php

/**
 * Unit tests for Foo
 *
 * @package      Cra\BlemmyaePpworks\Tests\Unit
 * @author       Gary Jones
 * @copyright    2021 CRA
 * @license      proprietary
 */

declare(strict_types=1);

namespace Cra\BlemmyaePpworks\Tests\Unit;

use Cra\BlemmyaePpworks\Foo as Testee;
use Cra\BlemmyaePpworks\Tests\TestCase;

/**
 * Foo test case.
 */
class FooTest extends TestCase
{
    /**
     * A single example test.
     */
    public function testSample(): void
    {
        // Replace this with some actual testing code.
        static::assertTrue((new Testee())->isTrue());
    }

    /**
     * A single example test.
     */
    public function testFoo(): void
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
        static::assertEquals('Foo::bar()', (new Testee())->bar());
    }
}
