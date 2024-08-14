<?php

declare(strict_types=1);

use Cra\BlemmyaeDeployment\A9SMigration;
use Cra\BlemmyaeDeployment\SetupMappingsTable;
use ReleaseInstructions\Command\CoreCommand as RI;
use Scm\Tools\Logger;
use Scm\Tools\Utils;

function a9s22_migration_ri_001(): void
{
    (new RI())->setStatus('a9s22_migration_ri_002', true);
    (new RI())->setStatus('a9s22_migration_ri_003', true);
}

/**
 * Migrate MSSP Alert Taxonomy to Blemmyae taxonomy.
 *
 * @return void
 * @throws Exception
 */
function a9s22_migration_ri_002(): void
{
    Logger::log('Process MSSP Alert terms.', 'status');
    $alertPath = '/a9s114/category_term_mappings_alert.csv';
    a9s22_migrate_taxonomy(
        A9SMigration::ALERT_DB_NAME,
        A9SMigration::ALERT_CATEGORY_TAX_MAPPING_TABLE,
        A9SMigration::ALERT_ID_COLUMN,
        $alertPath
    );
}

/**
 * Migrate Channel E2E Taxonomy to Blemmyae taxonomy.
 *
 * @return void
 * @throws Exception
 */
function a9s22_migration_ri_003(): void
{
    Logger::log('Process Channel E2E terms.', 'status');
    $ce2ePath = '/a9s114/category_term_mappings_ce2e.csv';
    a9s22_migrate_taxonomy(
        A9SMigration::CE2E_DB_NAME,
        A9SMigration::CE2E_CATEGORY_TAX_MAPPING_TABLE,
        A9SMigration::CE2E_ID_COLUMN,
        $ce2ePath
    );
}

/**
 * Migrate MSSP Alert and Channel E2E Taxonomy to Blemmyae taxonomy.
 *
 * @param string $dbName
 * @param string $tableName
 * @param string $oldIdColumn
 * @param string $map
 * @return void
 * @throws Exception
 */
function a9s22_migrate_taxonomy(
    string $dbName,
    string $tableName,
    string $oldIdColumn,
    string $map,
    bool $force = false
): void {
    // Fix memory leak.
    wp_suspend_cache_addition(true);

    $utils = new Utils();

    $utils->switchDB($dbName);
    $alertTerms = get_terms([
        'taxonomy' => 'category',
        'orderby' => 'include',
        'hide_empty' => false
    ]);

    $utils->switchDBToDefault();

    $itemsCount = sizeof($alertTerms);
    $count = 1;

    // Create mapping table.
    $setupMappingsTable = new SetupMappingsTable();
    $setupMappingsTable->setupTable(
        $tableName,
        $oldIdColumn,
        A9SMigration::BLEMMYAE_ID_COLUMN
    );

    // Get taxonomy term mappings.
    $termMapping = a9s22_mapping_from_csv($map);
    $terms = get_terms(['taxonomy' => 'topic', 'fields' => 'id=>name', 'hide_empty' => false,]);

    // Map old term and new term save ids mapping
    foreach ($alertTerms as $term) {
        $msgPrefix = '[' . $count . '/' . $itemsCount . ']';

        $termName = $term->name;
        $newTermName = $termMapping[$termName] ?? null;

        if ($newTermName) {
            $newTermId = array_search(htmlentities($newTermName), $terms);

            if (!$newTermId) {
                Logger::log(
                    $msgPrefix . ' Failed to get term by name: ' . $newTermName,
                    'notice'
                );
            }

            // Save a9s_id -> blem_id mapping.
            $result = A9SMigration::saveIdMapping(
                $tableName,
                $oldIdColumn,
                $term->term_id,
                $newTermId ?? ''
            );

            if (!$result) {
                Logger::log(
                    "Could not insert mapping pair for term with id: $term->term_id",
                    'notice'
                );
            }
        } else {
            Logger::log(
                "Mapping pair for term name $termName does not exist.",
                'notice'
            );
        }

        Logger::log(
            'Processed ' . $msgPrefix . ' terms, current term id: ' . $term->term_id,
            '
            status'
        );
        $count++;
    }

    wp_suspend_cache_addition(false);
}

/**
 * Generate oldTerm => newTerm mapping array from csv.
 *
 * @param string $path
 * @return array
 */
function a9s22_mapping_from_csv(string $path): array
{
    $map = Utils::importCsv(__DIR__ . $path, ';');
    $newMap = [];

    foreach ($map as $mapping) {
        $newMap[$mapping[0]] = $mapping[1];
    }

    return $newMap;
}
