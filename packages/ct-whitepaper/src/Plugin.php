<?php

/**
 * Main plugin file
 *
 * @package   Cra\CtWhitepaper
 * @author    Squiz Pty Ltd <products@squiz.net>
 * @copyright 2021 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   proprietary
 */

declare(strict_types=1);

namespace Cra\CtWhitepaper;

use BrightNucleus\Config\ConfigInterface;
use BrightNucleus\Config\ConfigTrait;
use BrightNucleus\Config\Exception\FailedToProcessConfigException;
use BrightNucleus\Settings\Settings;
use Scm\Tools\PostByQueryResolver;

use function add_action;
use function add_filter;
use function load_plugin_textdomain;

/**
 * Main plugin class.
 *
 * @since 0.1.0
 *
 * @package Cra\CtWhitepaper
 * @author  Gary Jones
 */
class Plugin
{
    use ConfigTrait;

    /**
     * Static instance of the plugin.
     *
     * @since 0.1.0
     *
     * @var self
     */
    protected static Plugin $instance;

    /**
     * Instantiate a Plugin object.
     *
     * Don't call the constructor directly, use the `Plugin::get_instance()`
     * static method instead.
     *
     * @param ConfigInterface $config Config to parametrize the object.
     *
     * @throws FailedToProcessConfigException If the Config could not be parsed correctly.
     *
     * @since 0.1.0
     *
     */
    public function __construct(ConfigInterface $config)
    {
        $this->processConfig($config);
        add_action('init', [$this, 'registerPostStatus']);
    }

    /**
     * Register post status for whitepapers that should not be displayed in feeds
     *
     * @return void
     */
    public function registerPostStatus(): void
    {
        // @todo: consider merging with ppworks `unfinished` post status
        register_post_status(
            WhitepaperCT::HIDDEN_FROM_FEEDS_POST_STATUS,
            [
                'label' => __('Hidden From Feeds', 'ct-whitepaper'),
                'exclude_from_search' => true,
                'public' => true,
            ]
        );
    }

    /**
     * Launch the initialization process.
     *
     * @since 0.1.0
     */
    public function run(): void
    {
        add_action('plugins_loaded', [$this, 'loadTextDomain']);

        $this->addFiltersToMakeReadOnlyFields();

        new PostByQueryResolver(WhitepaperCT::POST_TYPE);

        new WhitepaperCT();
    }

    /**
     * Load the plugin text domain.
     *
     * @since 0.1.0
     */
    public function loadTextDomain(): void
    {
        /**
         * Plugin text domain.
         *
         * @var string $textDomain
         */
        $textDomain = $this->config->getKey('Plugin.textdomain');
        $languagesDir = 'languages';
        if ($this->config->hasKey('Plugin/languages_dir')) {
            /**
             * Directory path.
             *
             * @var string $languagesDir
             */
            $languagesDir = $this->config->getKey('Plugin.languages_dir');
        }

        load_plugin_textdomain($textDomain, false, $textDomain . '/' . $languagesDir);
    }

    /**
     * Adds filters to make read-only ACF fields.
     */
    public function addFiltersToMakeReadOnlyFields(): void
    {
        $readOnlyFields = [
            /** @phpstan-ignore-next-line */
            Whitepaper::FIELD_CONVERTR_CAMPAIGN_ID,
            /** @phpstan-ignore-next-line */
            Whitepaper::FIELD_CONVERTR_CAMPAIGN_API_KEY,
            /** @phpstan-ignore-next-line */
            Whitepaper::FIELD_CONVERTR_LINK_ID,
            /** @phpstan-ignore-next-line */
            Whitepaper::FIELD_CONVERTR_DOWNLOAD_LINK,
            /** @phpstan-ignore-next-line */
            Whitepaper::FIELD_CONVERTR_WHITEPAPER_ID,
            /** @phpstan-ignore-next-line */
            Whitepaper::FIELD_CONVERTR_FORM_ID,
            /** @phpstan-ignore-next-line */
            Whitepaper::FIELD_CONVERTR_FORM_FIELDS,
        ];
        foreach ($readOnlyFields as $field) {
            add_filter("acf/load_field/key=$field", [$this, 'acfReadOnly']);
        }
    }

    /**
     * Callback for "acf/load_field" filter.
     *
     * @param array $field
     *
     * @return array
     */
    public function acfReadOnly(array $field): array
    {
        $field['readonly'] = 1;

        return $field;
    }
}
