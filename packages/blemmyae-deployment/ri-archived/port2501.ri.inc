<?php

/** @noinspection PhpUnused */

/**
 * @link: https://cra.myjetbrains.com/youtrack/issue/PORT-2501
 */

declare(strict_types=1);

use ReleaseInstructions\Command\CoreCommand as RI;
use Scm\Tools\Utils;

function port2501_blemmyae_deployment_ri_001(): void
{
    (new RI())->setStatus('port2501_blemmyae_deployment_ri_002', true);
    (new RI())->setStatus('port2501_blemmyae_deployment_ri_003', true);
    (new RI())->setStatus('port2501_blemmyae_deployment_ri_004', true);
}

/**
 * Re-import deleted Advanced Ads Groups.
 *
 * @return void
 */
function port2501_blemmyae_deployment_ri_002(): void
{
    $adGroups = array_map(
        static fn(array $row) => [
            'term_name' => $row[1],
        ],
        Utils::importCsv(__DIR__ . '/port2501/advanced_ads_groups.csv')
    );

    foreach ($adGroups as $item) {
        Utils::insertTaxonomyTerm($item['term_name'], 'advanced_ads_groups');
    }

    wp_cache_flush();
}

/**
 * Re-import deleted Job Titles.
 *
 * @return void
 */
function port2501_blemmyae_deployment_ri_003(): void
{
    $adGroups = array_map(
        static fn(array $row) => [
            'term_name' => $row[1],
        ],
        Utils::importCsv(__DIR__ . '/port2501/job_title.csv')
    );

    foreach ($adGroups as $item) {
        Utils::insertTaxonomyTerm($item['term_name'], 'job_title');
    }

    wp_cache_flush();
}

/**
 * Re-import deleted Whitepaper Types.
 *
 * @return void
 */
function port2501_blemmyae_deployment_ri_004(): void
{
    $adGroups = array_map(
        static fn(array $row) => [
            'term_name' => $row[1],
        ],
        Utils::importCsv(__DIR__ . '/port2501/whitepaper_type.csv')
    );

    foreach ($adGroups as $item) {
        Utils::insertTaxonomyTerm($item['term_name'], 'whitepaper_type');
    }

    wp_cache_flush();
}
