<?php

/**
 * Advanced Ads – Options.
 *
 * @author Alexander Kucherov (avdkucherov@gmail.com)
 */

declare(strict_types=1);

namespace Scm\Advanced_Ads;

use function add_filter;
use function register_meta;

class Options
{
    /**
     * Initialize hooks.
     */
    public function __construct()
    {
        // Beware! Dirty hack - hide ads from free version of `Advanced Ads` plugin.
        // It is allowed to do this kind of change due to GNU v2 license.
        if (!defined('AAP_VERSION')) {
            define('AAP_VERSION', true);
        }
        if (!defined('AAGAM_VERSION')) {
            define('AAGAM_VERSION', true);
        }

        $this->hookInit();
    }

    /**
     * Advanced Ads custom hook calls.
     *
     * @return $this Options instance.
     */
    public function hookInit(): self
    {
        // @todo: Update filters description for autodocs.
        add_filter(
            'register_post_type_args',
            static function ($args, $postType) {
                if ($postType === 'advanced_ads') {
                    $args['public'] = true;
                    $args['publicly_queryable'] = true;
                    $args['show_in_rest'] = true;
                    $args['show_in_graphql'] = true;
                    $args['graphql_single_name'] = 'advancedAd';
                    $args['graphql_plural_name'] = 'advancedAds';
                }

                return $args;
            },
            10,
            2
        );

        // @todo: Update filters description for autodocs.
        add_filter(
            'register_taxonomy_args',
            static function ($args, $taxonomy) {
                if ($taxonomy === 'advanced_ads_groups') {
                    $args['public'] = true;
                    $args['publicly_queryable'] = true;
                    $args['show_in_rest'] = true;
                    $args['show_in_graphql'] = true;
                    $args['graphql_single_name'] = 'advancedAdGroup';
                    $args['graphql_plural_name'] = 'advancedAdsGroups';
                }

                return $args;
            },
            10,
            2
        );

        foreach ($this->metaFields() as $metaField) {
            register_meta(
                'advanced_ads',
                $metaField['key'],
                [
                    'type' => $metaField['type'] ?? 'string',
                    'description' => $metaField['label'],
                    'single' => true,
                    'show_in_rest' => true,
                ]
            );
        }

        return $this;
    }

    /**
     * Definitions for advanced ads meta fields.
     *
     * @return array
     */
    public function metaFields(): array
    {
        return [
            [
                'key' => 'expired',
                'label' => __('Has ad expired', 'administration'),
                'type' => 'boolean',
            ],
            [
                'key' => 'content',
                'label' => __('Ad Content', 'administration'),
            ],
        ];
    }
}
