<?php

/**
 * @link: https://cra.myjetbrains.com/youtrack/issue/BLEM-9
 */

/** @noinspection PhpUnused */

declare(strict_types=1);

const TAXONOMY_WHITEPAPER_TYPE = 'whitepaper_type';

use Scm\Tools\Logger;
use Scm\Tools\Utils;

/**
 * Import Whitepaper Type flaxonomy.
 */
function ct_whitepaper_ri_010(): void
{
    $types = [
        'Company',
        'Editorial',
        'Partner',
    ];

    foreach ($types as $type) {
        Utils::insertTaxonomyTerm($type, TAXONOMY_WHITEPAPER_TYPE);
    }
    wp_cache_flush();
}

/**
 * Import Whitepaper Sync Options.
 */
function ct_whitepaper_ri_012(): void
{
    $convertrCampaigns = array_map(
        fn(array $row) => [
            'name' => $row[1],
            'id' => $row[2],
            'api_key' => $row[3],
        ],
        Utils::importCsv(__DIR__ . '/convertr_campaigns.csv')
    );

    try {
        $success = update_field(
            'convertr_campaigns',
            $convertrCampaigns,
            'options'
        );
        if ($success) {
            Logger::log('Updated convertr_campaigns', 'success');
        } else {
            Logger::log('Skipped convertr_campaigns', 'warning');
        }
    } catch (Exception $exception) {
        Logger::log('Error updating convertr_campaigns: ' . $exception->getMessage(), 'error');
    }
    wp_cache_flush();
}
