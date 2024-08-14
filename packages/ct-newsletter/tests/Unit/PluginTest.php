<?php

/**
 * Unit tests for Plugin
 *
 * @package      Cra\CtNewsletter\Tests\Unit
 * @author       Gary Jones
 * @copyright    2021 CRA
 * @license      proprietary
 */

declare(strict_types=1);

namespace Cra\CtNewsletter\Tests\Unit;

use Brain\Monkey\Functions;
use BrightNucleus\Config\ConfigFactory;
use BrightNucleus\Config\ConfigInterface;
use Cra\CtNewsletter\Plugin as Testee;
use Cra\CtNewsletter\Tests\TestCase;

/**
 * Foo test case.
 */
class PluginTest extends TestCase
{
    /**
     * The method inside the Plugin class which calls `load_plugin_textdomain()`.
     *
     * @var string
     */
    private string $loadTextDomainCallback;

    /**
     * Plugin config for these unit tests.
     *
     * @var ConfigInterface
     */
    private ConfigInterface $mockConfig;

    /**
     * Prepares the test environment before each test.
     *
     * @since 1.0.0
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->loadTextDomainCallback = 'loadTextDomain';

        $mockConfig = [
            'Settings' => [],
            'Plugin' => [
                'textdomain' => 'apple',
                'languages_dir' => 'banana',
            ],
        ];

        $this->mockConfig = ConfigFactory::createFromArray($mockConfig);

        parent::setUp();
    }

    /**
     * Test that method that calls load_plugin_textdomain is hooked in to to the correct hook.
     */
    public function testLoadPluginTextDomainMethodIsHookedInCorrectly(): void
    {
        // Create an instance of the class under test.
        $plugin = new Testee($this->mockConfig);
        $plugin->run();

        // Check the plugin method that loads the text domain is hooked into the right filter.
        static::assertNotFalse(
            has_action('plugins_loaded', [$plugin, $this->loadTextDomainCallback]),
            'Loading textdomain is not hooked in correctly.'
        );
    }

    /**
     * Test that load_plugin_textdomain() is called with the correct configurable arguments.
     */
    public function testLoadPluginTextdomainCalledWithCorrectArgs(): void
    {
        Functions\expect('load_plugin_textdomain')
            ->once()
            ->with('apple', false, 'apple/banana');

        // Create an instance of the class under test.
        $plugin = new Testee($this->mockConfig);
        $plugin->{$this->loadTextDomainCallback}();
    }
}
