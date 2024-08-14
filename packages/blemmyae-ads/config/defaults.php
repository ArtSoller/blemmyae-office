<?php

/**
 * Plugin configuration file
 *
 * @package   Cra\BlemmyaeAds
 * @author    Squiz Pty Ltd <products@squiz.net>
 * @copyright 2021 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   proprietary
 */

declare(strict_types=1);

namespace Cra\BlemmyaeAds;

$blemmyaeAdsPlugin = [
    'textdomain' => 'blemmyae-ads',
    'languages_dir' => 'languages',
];

$blemmyaeAdsSettings = [
    'submenu_pages' => [
        [
            'parent_slug' => 'options-general.php',
            'page_title' => __('Blemmyae Ads Settings', 'blemmyae-ads'),
            'menu_title' => __('Blemmyae Ads', 'blemmyae-ads'),
            'capability' => 'manage_options',
            'menu_slug' => 'blemmyae-ads',
            'view' => BLEMMYAE_ADS_DIR . 'views/admin-page.php',
            'dependencies' => [
                'styles' => [],
                'scripts' => [
                    [
                        'handle' => 'blemmyae-ads-js',
                        'src' => BLEMMYAE_ADS_URL . 'js/admin-page.js',
                        'deps' => ['jquery'],
                        'ver' => '1.2.3',
                        'in_footer' => true,
                        'is_needed' => static function (array $context): bool {
                            if ($context) {
                                return false;
                            }

                            return true;
                        },
                        'localize' => [
                            'name' => 'blemmyaeAdsI18n',
                            'data' => static function (array $context): array {
                                return [
                                    'test_localize_data' => 'test_localize_value',
                                    'context' => $context,
                                ];
                            },
                        ],
                    ],
                ],
                'handlers' => [
                    'scripts' => 'BrightNucleus\Dependency\ScriptHandler',
                    'styles' => 'BrightNucleus\Dependency\StyleHandler',
                ],
            ],
        ],
    ],
    'settings' => [
        'setting1' => [
            'option_group' => 'blemmyaeads',
            'sanitize_callback' => null,
            'sections' => [
                'section1' => [
                    'title' => __('My Section Title', 'blemmyae-ads'),
                    'view' => BLEMMYAE_ADS_DIR . 'views/section1.php',
                    'fields' => [
                        'field1' => [
                            'title' => __('My Field Title', 'blemmyae-ads'),
                            'view' => BLEMMYAE_ADS_DIR . 'views/field1.php',
                        ],
                    ],
                ],
            ],
        ],
    ],
];

return [
    'Cra' => [
        'BlemmyaeAds' => [
            'Plugin' => $blemmyaeAdsPlugin,
            'Settings' => $blemmyaeAdsSettings,
        ],
    ],
];
