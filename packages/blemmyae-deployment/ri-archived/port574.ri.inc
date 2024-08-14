<?php

/**
 * @link: https://cra.myjetbrains.com/youtrack/issue/PORT-574
 */

declare(strict_types=1);

use ReleaseInstructions\Command\CoreCommand as RI;
use Scm\Tools\Utils;

function port574_blemmyae_deployment_ri_001(): void
{
    (new RI())->setStatus('port574_blemmyae_deployment_ri_002', true);
}

function port574_blemmyae_deployment_ri_002(): void
{
    $topicTaxonomy = array_map(
        static fn(array $row) => [
            'year' => $row[0],
        ],
        Utils::importCsv(__DIR__ . '/port574/year_taxonomy.csv')
    );

    foreach ($topicTaxonomy as $item) {
        Utils::insertTaxonomyTerm($item['year'], 'year');
    }

    wp_cache_flush();
}
