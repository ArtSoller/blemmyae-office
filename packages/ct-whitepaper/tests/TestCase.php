<?php

/**
 * Base unit test case
 *
 * @package      Cra\CtWhitepaper\Tests
 * @author       Gary Jones
 * @copyright    2021 CRA
 * @license      proprietary
 */

declare(strict_types=1);

namespace Cra\CtWhitepaper\Tests;

use Brain\Monkey;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

/**
 * Abstract base class for all test case implementations.
 *
 * @package Cra\CtWhitepaper\Tests
 * @since   1.0.0
 */
abstract class TestCase extends PHPUnitTestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * Prepares the test environment before each test.
     *
     * @return void
     * @since 1.0.0
     *
     */
    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();
    }

    /**
     * Cleans up the test environment after each test.
     *
     * @return void
     * @since 1.0.0
     *
     */
    protected function tearDown(): void
    {
        Monkey\tearDown();
        parent::tearDown();
    }
}
