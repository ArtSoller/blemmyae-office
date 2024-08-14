<?php

declare(strict_types=1);

use Scm\Tools\WpCore;
use Scm\Tools\Logger;
use ReleaseInstructions\Command\CoreCommand as RI;
use Scm\Tools\Utils;

const A9S159_TERMS = [
    'Ce2e-Sponsor-Ad',
    'Ce2e-Sponsor-Appriver',
    'Ce2e-Sponsor-Cisco',
    'Ce2e-Sponsor-Connectwise',
    'Ce2e-Sponsor-Datto',
    'Ce2e-Sponsor-Itglue',
    'Ce2e-Sponsor-Ninjarmm',
    'Ce2e-Sponsor-Solarwindsmsp',
    'Ce2e-Sponsor-Vipre',
    'Ce2e-Sponsor-Webroot',
    'Ce2e-Sponsor-Continuum',
    'Ce2e-Sponsor-Servicetree',
    'Ce2e-Sponsor-Acronis',
    'Ce2e-Sponsor-Sherweb',
    'Ce2e-Sponsor-Dell',
    'Ce2e-Sponsor-7figmsp',
    'Ce2e-Sponsor-Axcient',
    'Ce2e-Sponsor-Itbydesign',
    'Ce2e-Sponsor-Egnyte',
    'Ce2e-Sponsor-Sophos',
    'Ce2e-Sponsor-Focus',
    'Ce2e-Sponsor-Atera',
    'Ce2e-Sponsor-Superopsai',
    'Ce2e-Sponsor-Watchguard',
    'Ce2e-Sponsor-Addigy',
    'Ce2e-Sponsor-Evergreen',
    'Ce2e-Sponsor-Liongard',
    'Ce2e-Sponsor-N-able',
    'Ce2e Egnyte',
    'Ce2e Cisco',
    'Ce2e Acronis',
    'Ce2e Dell',
    'Ce2e IT by Design',
    'Ce2e WatchGuard',
    'Ce2e Liongard',
    'Ce2e Evergreen',
    'Ce2e Webroot',
    'Ce2e Sherweb',
    'Ce2e N-able',
    'Ce2e Atera',
    'Ce2e ConnectWise',
    'Ce2e Focus Investement Bankers',
    'Ce2e TimeZest',
    'Ce2e Kaspersky',
    'Mssp Apptega',
    'Mssp Blackberry',
    'Mssp Cofense',
    'Mssp Netenrich',
    'Mssp DELL',
    'Mssp Fortinet',
    'Mssp Huntress',
    'Mssp N-able',
    'Mssp Netsurion',
    'Mssp Arctic Wolf',
    'Mssp Sophos',
    'Mssp StellarCyber',
    'Mssp Invicti',
    'Mssp Webroot',
    'Mssp D3Security',
    'Mssp Sherweb',
    'Mssp ATT',
    'Mssp Blueshift Cyber',
    'Mssp TimeZest',
    'Mssp Kaspersky',
];

function a9s159_migration_ri_004(): void
{
    (new RI())->setStatus('a9s159_migration_ri_002', true);
    (new RI())->setStatus('a9s159_migration_ri_003', true);
}

/**
 * Generate new Ads.
 * @throws Exception
 */
function a9s159_migration_ri_005(): void
{
    if (Utils::isProd()) {
        return;
    }

    foreach (A9S159_TERMS as $termName) {
        $term = get_term_by('name', $termName, Advanced_Ads::AD_GROUP_TAXONOMY);

        if (!$term) {
            Logger::log('Unable to get term id for Ad Group: ' . $termName, 'notice');
            continue;
        }

        $termId = $term->term_id;

        $args = [
            'post_type' => Advanced_Ads::POST_TYPE_SLUG,
            'fields' => 'ids',
            'tax_query' => [
                [
                    'taxonomy' => Advanced_Ads::AD_GROUP_TAXONOMY,
                    'field' => 'term_id',
                    'terms' => $termId
                ]
            ]
        ];
        $query = new WP_Query($args);
        if ($query->have_posts()) {
            foreach ($query->get_posts() as $postId) {
                wp_delete_post($postId);
                Logger::log('Removed old test Ad with id: ' . $postId, 'success');
            }
        }

        if (!class_exists('Advanced_Ads_Ad')) {
            continue;
        }

        for ($i = 0; $i < 2; $i++) {
            $random = bin2hex(random_bytes(5));
            $data = array(
                'post_title' => 'Test Ad ' . $random,
                'post_content' => '<p>Test Ad ' . $random . '</p>',
                'post_status' => 'publish',
                'post_type' => Advanced_Ads::POST_TYPE_SLUG,
                'post_author' => 38406,
            );

            $newPost = WpCore::insertPost($data);

            wp_set_post_terms($newPost->ID, $termId, Advanced_Ads::AD_GROUP_TAXONOMY);

            (new Advanced_Ads_Ad($newPost->ID))->save();
            wp_cache_flush();
            Logger::log('Generated new Ad: ' . $newPost->post_title, 'success');
        }
    }
}

/**
 * Delete AdGroups.
 * @throws Exception
 */
function a9s159_migration_ri_003(): void
{
    if (Utils::isProd()) {
        return;
    }

    foreach (A9S159_TERMS as $termName) {
        $term = get_term_by('name', $termName, Advanced_Ads::AD_GROUP_TAXONOMY);

        if (!$term) {
            Logger::log('Unable to get term id for Ad Group: ' . $termName, 'notice');
            continue;
        }

        $termId = $term->term_id;

        wp_delete_term($termId, Advanced_Ads::AD_GROUP_TAXONOMY);
        wp_cache_flush();
        Logger::log('Deleted term: ' . $termName, 'success');
    }
}
