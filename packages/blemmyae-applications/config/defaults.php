<?php

/**
 * Plugin configuration file
 *
 * @package   Cra\BlemmyaeApplications
 * @author    Squiz Pty Ltd <products@squiz.net>
 * @copyright 2021 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   proprietary
 */

declare(strict_types=1);

namespace Cra\BlemmyaeApplications;

$blemmyaeApplicationsPlugin = [
    'textdomain' => 'blemmyae-applications',
    'languages_dir' => 'languages',
];

$blemmyaeApplicationsSettings = [
    'submenu_pages' => [
        [
            'parent_slug' => 'options-general.php',
            'page_title' => __('Blemmyae Applications Settings', 'blemmyae-applications'),
            'menu_title' => __('Blemmyae Applications', 'blemmyae-applications'),
            'capability' => 'manage_options',
            'menu_slug' => 'blemmyae-applications',
            'view' => BLEMMYAE_APPLICATIONS_DIR . 'views/admin-page.php',
            'dependencies' => [
                'styles' => [],
                'scripts' => [
                    [
                        'handle' => 'blemmyae-applications-js',
                        'src' => BLEMMYAE_APPLICATIONS_URL . 'js/admin-page.js',
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
                            'name' => 'blemmyaeApplicationsI18n',
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
            'option_group' => 'blemmyaeapplications',
            'sanitize_callback' => null,
            'sections' => [
                'section1' => [
                    'title' => __('My Section Title', 'blemmyae-applications'),
                    'view' => BLEMMYAE_APPLICATIONS_DIR . 'views/section1.php',
                    'fields' => [
                        'field1' => [
                            'title' => __('My Field Title', 'blemmyae-applications'),
                            'view' => BLEMMYAE_APPLICATIONS_DIR . 'views/field1.php',
                        ],
                    ],
                ],
            ],
        ],
    ],
];

return [
    'Cra' => [
        'BlemmyaeApplications' => [
            'Plugin' => $blemmyaeApplicationsPlugin,
            'Settings' => $blemmyaeApplicationsSettings,
        ],
    ],
];
