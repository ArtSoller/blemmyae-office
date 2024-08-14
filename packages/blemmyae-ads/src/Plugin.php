<?php

/**
 * Main plugin file
 *
 * @package   Cra\BlemmyaeAds
 * @author    Squiz Pty Ltd <products@squiz.net>
 * @copyright 2021 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   proprietary
 */

declare(strict_types=1);

namespace Cra\BlemmyaeAds;

use BrightNucleus\Config\ConfigInterface;
use BrightNucleus\Config\ConfigTrait;
use BrightNucleus\Config\Exception\FailedToProcessConfigException;
use BrightNucleus\Settings\Settings;
use WPGraphQL\Model\Post;
use WPGraphQL\Model\Term;

use function add_action;
use function load_plugin_textdomain;

/**
 * Main plugin class.
 *
 * @since 0.1.0
 *
 * @package Cra\BlemmyaeAds
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
    }

    /**
     * Launch the initialization process.
     *
     * @since 0.1.0
     */
    public function run(): void
    {
        add_action('plugins_loaded', [$this, 'loadTextDomain']);
        add_action('graphql_register_types', [$this, 'graphqlInlineAds'], 10);
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
         * @var string
         */
        $textDomain = $this->config->getKey('Plugin.textdomain');
        $languagesDir = 'languages';
        if ($this->config->hasKey('Plugin/languages_dir')) {
            /**
             * Directory path.
             *
             * @var string
             */
            $languagesDir = $this->config->getKey('Plugin.languages_dir');
        }

        load_plugin_textdomain($textDomain, false, $textDomain . '/' . $languagesDir);
    }

    /**
     * Registers GraphQL inlineAdSettings type.
     */
    public function graphqlInlineAds(): void // phpcs:ignore Inpsyde.CodeQuality.FunctionLength.TooLong
    {
        register_graphql_object_type(
            'InlineAdQueryArray',
            [
                'description' => __('Taxonomy Array', 'blemmyae-ads'),
                'fields' => [
                    'operator' => ['type' => 'String'],
                    'terms' => ['type' => ['list_of' => 'termNode']],
                ],
            ]
        );

        register_graphql_object_type(
            'InlineAdTaxonomyQuery',
            [
                'description' => __('Taxonomy Query', 'blemmyae-ads'),
                'fields' => [
                    'relation' => ['type' => 'String'],
                    'queryArray' => ['type' => ['list_of' => 'InlineAdQueryArray']],
                ],
            ]
        );

        register_graphql_object_type(
            'InlineAdPostTypes',
            [
                'description' => __('Post Types', 'blemmyae-ads'),
                'fields' => [
                    'type' => ['type' => ['list_of' => 'String']],
                    'app' => ['type' => 'String'],
                ],
            ]
        );

        register_graphql_object_type(
            'InlineAd',
            [
                'description' => __('Settings for an inline ad.', 'blemmyae-ads'),
                'fields' => [
                    'adminTitle' => ['type' => 'String'],
                    'adUnitName' => ['type' => 'String'],
                    'sid' => ['type' => 'String'],
                    'targetingId' => ['type' => 'String'],
                    'position' => ['type' => 'Integer'],
                    'maxPerPost' => ['type' => 'Integer'],
                    'postTypes' => ['type' => 'InlineAdPostTypes'],
                    'taxonomyQuery' => ['type' => 'InlineAdTaxonomyQuery'],
                    'postsToInclude' => ['type' => ['list_of' => 'DatabaseIdentifier']],
                    'postsToExclude' => ['type' => ['list_of' => 'DatabaseIdentifier']],
                ],
            ]
        );
        register_graphql_field(
            'RootQuery',
            'inlineAds',
            [
                'type' => ['list_of' => 'InlineAd'],
                'description' => __('List of all inline ads', 'blemmyae-ads'),
                'resolve' => static function (): array {
                    $ads = get_fields('inline_ad_settings');

                    // phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable
                    // phpcs:disable Inpsyde.CodeQuality.ElementNameMinimalLength.TooShort
                    // phpcs:disable Inpsyde.CodeQuality.LineLength.TooLong
                    return array_map(
                        static fn($ad) => [
                            'adminTitle' => $ad['admin_title'],
                            'adUnitName' => $ad['ad_unit_name'],
                            'sid' => $ad['sid'] ?? '',
                            'targetingId' => $ad['targeting_id'] ?? '',
                            'position' => (int)$ad['position'],
                            'maxPerPost' => (int)$ad['advanced_filters']['max_per_post'],
                            'postTypes' => [
                                'type' => $ad['basic_filters']['post_type'] ?? [],
                                'app' => $ad['application'] instanceof \WP_Term ? $ad['application']->slug : '',
                            ],
                            'taxonomyQuery' => empty($ad['basic_filters']['taxonomy_query']['query_array']) ?
                                null :
                                [
                                    'relation' => $ad['basic_filters']['taxonomy_query']['relation'],
                                    'queryArray' => array_map(
                                        static fn($query) => [
                                            'operator' => $query['operator'],
                                            'terms' => array_map(static fn($term) => new Term($term), $query['terms']),
                                        ],
                                        $ad['basic_filters']['taxonomy_query']['query_array']
                                    ),
                                ],
                            'postsToInclude' => array_map(
                                static fn($post) => new Post($post),
                                $ad['advanced_filters']['posts_to_include'] ?? []
                            ),
                            'postsToExclude' => array_map(
                                static fn($post) => new Post($post),
                                $ad['advanced_filters']['posts_to_exclude'] ?? []
                            ),
                        ],
                        $ads['ads'] ?? []
                    );
                    // phpcs:enable VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable
                    // phpcs:enable Inpsyde.CodeQuality.ElementNameMinimalLength.TooShort
                    // phpcs:enable Inpsyde.CodeQuality.LineLength.TooLong
                },
            ]
        );
    }
}
