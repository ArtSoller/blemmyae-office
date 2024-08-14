<?php

/**
 * @link: https://cra.myjetbrains.com/youtrack/issue/BLEM-604
 */

declare(strict_types=1);

use ReleaseInstructions\Command\CoreCommand as RI;
use Scm\Tools\Utils;

function blem604_insert_term($termName, $parentName = ''): void
{
    if (empty($termName)) {
        return;
    }

    $parentObject = null;
    if ($parentName) {
        $parentObject = get_term_by('name', $parentName, 'sc_award');
        // Normalize result.
        if (is_array($parentObject)) {
            $parentObject = array_shift($parentObject);
        }
    }

    $description = 'Please, update description for ' . $termName . '.';

    $termSlug = sanitize_title_with_dashes($termName) . ($termName !== '2021' ? '-2021' : '');
    $existingTerm = get_term_by('slug', $termSlug, 'sc_award');
    if ($existingTerm) {
        return;
    }

    $args = [
        'description' => $description,
        'slug' => $termSlug,
        'parent' => ($parentObject instanceof WP_Term) ? $parentObject->term_id : 0,
    ];

    wp_insert_term(
        $termName,
        'sc_award',
        $args
    );
}


function blem604_1_blemmyae_deployment_ri_001(): void
{
    (new RI())->setStatus('blem604_1_blemmyae_deployment_ri_002', true);
}

function blem604_1_blemmyae_deployment_ri_002(): void
{
    $topicTaxonomy = array_map(
        static fn(array $row) => [
            'year' => $row[0],
            'category' => $row[1],
            'award' => $row[2],
        ],
        Utils::importCsv(__DIR__ . '/blem604/2021-sc-awards-taxonomy-update.csv')
    );

    foreach ($topicTaxonomy as $item) {
        blem604_insert_term($item['year']);
        blem604_insert_term($item['category'], $item['year']);
        blem604_insert_term($item['award'], $item['category']);
    }

    wp_cache_flush();
}
