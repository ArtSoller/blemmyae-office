<?php

declare(strict_types=1);

use Cra\BlemmyaeApplications\BlemmyaeApplications;

/**
 * @throws Exception
 */
function port2127_migration_ri_001(): void
{
    $terms = [
        'cra' => 'Cra'
    ];

    foreach ($terms as $slug => $name) {
        if (empty(term_exists($slug, BlemmyaeApplications::TAXONOMY))) {
            wp_insert_term($name, BlemmyaeApplications::TAXONOMY, [
                'slug' => $slug,
                'description' => sprintf('Taxonomy term for %s CERB application', $slug)
            ]);
        }
    }

    wp_cache_flush();
}
