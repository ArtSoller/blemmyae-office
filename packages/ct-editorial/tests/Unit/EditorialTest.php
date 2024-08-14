<?php

/**
 * Unit tests for Editorial
 *
 * @package      Cra\CtEditorial\Tests\Unit
 * @author       Gary Jones
 * @copyright    2021 CRA
 * @license      proprietary
 */

declare(strict_types=1);

namespace Cra\CtEditorial\Tests\Unit;

use Cra\CtEditorial\Editorial as Testee;
use Cra\CtEditorial\Tests\TestCase;

/**
 * Editorial test case.
 */
class EditorialTest extends TestCase
{
    /**
     * @test
     * @see: Testee::hookInit()
     */
    public function hookInit(): void
    {
        static::assertIsObject((new Testee())->hookInit());
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function hidePostObject(): void
    {
        (new Testee())->hidePostObject();
    }
}
