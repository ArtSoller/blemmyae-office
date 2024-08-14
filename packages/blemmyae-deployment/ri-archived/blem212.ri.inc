<?php

/**
 * @file Add menu items for CRA portal.
 *
 * @link https://cra.myjetbrains.com/youtrack/issue/BLEM-212
 */

declare(strict_types=1);

use Scm\Tools\Logger;

const BLEM_212_PRIMARY_MENU_ITEMS = [
    [
        'title' => 'Topics',
        'url' => '/topics',
        'children' => [
            ['title' => 'Strategy', 'url' => '/topic/strategy'],
            ['title' => 'Threats', 'url' => '/topic/threats'],
            ['title' => 'Policy', 'url' => '/topic/policy'],
            ['title' => 'Risk management', 'url' => '/topic/risk-management'],
            ['title' => 'Architecture', 'url' => '/topic/architecture'],
            ['title' => 'Application Security', 'url' => '/topic/application-security'],
            ['title' => 'APT', 'url' => '/topic/apt'],
            ['title' => 'Access management', 'url' => '/topic/access-management'],
            ['title' => 'Cloud', 'url' => '/topic/cloud'],
            ['title' => 'Compliance', 'url' => '/topic/compliance'],
            ['title' => 'Endpoint security', 'url' => '/topic/endpoint-security'],
            ['title' => 'IAM', 'url' => '/topic/iam'],
            ['title' => 'Incident response', 'url' => '/topic/incident-response'],
            ['title' => 'Network security', 'url' => '/topic/network-security'],
            ['title' => 'Ransomware', 'url' => '/topic/ransomware'],
            ['title' => 'Security awareness', 'url' => '/topic/security-awareness'],
            ['title' => 'SOC', 'url' => '/topic/soc'],
            ['title' => 'TDR', 'url' => '/topic/tdr'],
            ['title' => 'Third-party risk', 'url' => '/topic/third-party-risk'],
            ['title' => 'Threat intelligence', 'url' => '/topic/threat-intelligence'],
            ['title' => 'Training', 'url' => '/topic/training'],
            ['title' => 'Vulnerability management', 'url' => '/topic/vulnerability-management'],
            ['title' => 'Zero trust', 'url' => '/topic/zero-trust'],
        ],
    ],
    [
        'title' => 'Events',
        'url' => '#',
        'children' => [
            ['title' => 'RiskSec Conference', 'url' => 'https://www.risksecconference.com/'],
            ['title' => 'InfoSec World', 'url' => 'https://www.infosecworldusa.com/'],
            ['title' => 'Webcasts', 'url' => '#'],
            ['title' => 'Virtual Conferences', 'url' => '#'],
        ],
    ],
    [
        'title' => 'Podcasts',
        'url' => '#',
        'children' => [
            ['title' => "Paul's Security Weekly", 'url' => '#'],
            ['title' => 'Enterprise Security Weekly', 'url' => '#'],
        ],
    ],
    [
        'title' => 'Research',
        'url' => '#',
        'children' => [
            ['title' => 'CRA Business Intelligence', 'url' => '#'],
            ['title' => 'Resource center', 'url' => '#'],
            ['title' => 'SW Labs', 'url' => '#'],
        ],
    ],
    [
        'title' => 'Recognition',
        'url' => '#',
        'children' => [
            ['title' => 'Recognition', 'url' => '#'],
        ],
    ],
    [
        'title' => 'Peer to Peer',
        'url' => '#',
        'children' => [
            ['title' => 'Peer to Peer', 'url' => '#'],
        ],
    ],
];

const BLEM_212_FOOTER_MENU_ITEMS = [
    [
        'title' => 'About Us',
        'url' => '/about-sc-media',
        'children' => [
            ['title' => 'SC Media', 'url' => '/about-sc-media'],
            ['title' => 'CyberRisk Alliance', 'url' => 'https://www.cyberriskalliance.com'],
            ['title' => 'Contact Us', 'url' => '/contact-us'],
            ['title' => 'Privacy', 'url' => '/privacypolicy'],
        ],
    ],
    [
        'title' => 'Get Involved',
        'url' => '#',
        'children' => [
            ['title' => 'Subscribe', 'url' => 'https://cyberrisk.dragonforms.com/loading.do?omedasite=SCM_newsletter'],
            ['title' => 'Partner', 'url' => '#'],
            ['title' => 'Contribute/Speak', 'url' => '#'],
            ['title' => 'Attend an event', 'url' => '#'],
            ['title' => 'Join a peer group', 'url' => '#'],
        ],
    ],
    [
        'title' => 'Explore',
        'url' => '#',
        'children' => [
            ['title' => 'Product reviews', 'url' => '/product-review'],
            ['title' => 'Research', 'url' => '#'],
            ['title' => 'White papers', 'url' => '#'],
            ['title' => 'Webcasts', 'url' => '#'],
            ['title' => 'Podcasts', 'url' => '#'],
        ],
    ],
];

/**
 * Update menu with the exact list of items.
 *
 * @param string $name
 * @param string $location
 * @param array $items
 */
function _blem212_update_menu(string $name, string $location, array $items): void
{
    $menu = wp_get_nav_menu_object($name);
    if ($menu) {
        Logger::log("Deleting the existing menu $name", 'info');
        wp_delete_nav_menu($menu);
    }

    $menuId = wp_update_nav_menu_object(0, [
        'description' => 'Test before release',
        'menu-name' => $name,
        'menu-locations' => $location,
    ]);
    Logger::log("Menu ($menuId) has been successfully created!", 'success');

    foreach ($items as $item) {
        $menuItemId = wp_update_nav_menu_item($menuId, 0, [
            'menu-item-type' => $item['type'] ?? 'custom',
            'menu-item-title' => $item['title'],
            'menu-item-url' => $item['url'],
            'menu-item-status' => 'publish',
        ]);
        Logger::log("Menu item $menuItemId has been added to menu $menuId", 'success');
        foreach ($item['children'] ?? [] as $childItem) {
            $childItemId = wp_update_nav_menu_item($menuId, 0, [
                'menu-item-type' => $childItem['type'] ?? 'custom',
                'menu-item-title' => $childItem['title'],
                'menu-item-url' => $childItem['url'],
                'menu-item-status' => 'publish',
                'menu-item-parent-id' => $menuItemId,
            ]);
            Logger::log(
                "Submenu item $childItemId has been added to menu $menuId for parent $menuItemId",
                'success'
            );
        }
    }

    $locations = get_theme_mod('nav_menu_locations');
    $locations[$location] = $menuId;
    if (set_theme_mod('nav_menu_locations', $locations)) {
        Logger::log("Location '$location' has been set for menu $menuId successfully", 'success');
    } else {
        Logger::log("Menu locations haven't been updated!", 'error');
    }

}

/**
 * Add menu items for CRA portal.
 */
function blem212_blemmyae_deployment_ri_001(): void
{
    _blem212_update_menu('Primary', 'primary', BLEM_212_PRIMARY_MENU_ITEMS);
    _blem212_update_menu('Company Info Footer', 'footer', BLEM_212_FOOTER_MENU_ITEMS);
}
