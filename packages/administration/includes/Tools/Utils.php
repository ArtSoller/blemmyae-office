<?php

/**
 * Utils – Custom Functions.
 *
 * @author Alexander Kucherov (avdkucherov@gmail.com)
 */

declare(strict_types=1);

namespace Scm\Tools;

use Cra\BlemmyaeApplications\BlemmyaeApplications;
use DateTime;
use DateTimeZone;
use Exception;
use WP_Term;

class Utils
{
    public const LEARNING_TIMEZONE = 'America/New_York';

    public const ACF_DB_DATETIME_FORMAT = 'Y-m-d H:i:s'; // e.g. 1990-12-20 16:59:59

    /**
     * Default database name.
     *
     * @var string
     */
    private string $defaultDB = '';

    /**
     * Get current environment.
     *
     * @return string
     */
    public static function environment(): string
    {
        $env = 'COPILOT_ENVIRONMENT_NAME';
        defined($env) or define($env, getenv($env));
        return constant($env) ?: 'dev';
    }

    /**
     * Exports either 1- or 2-,3- level taxonomy as a CSV text ready for import.
     *
     * @param string $taxName
     * @param bool $hierarchical
     *
     * @return string|null
     */
    public static function exportTaxonomy(string $taxName = '', bool $hierarchical = false): ?string
    {
        if (!$taxName) {
            return null;
        }

        return $hierarchical ? self::exportTaxonomyHierarchical($taxName) : self::exportTaxonomyFlatten($taxName);
    }

    /**
     * Exports 1-level taxonomy as a CSV text ready for import.
     *
     * @param string $taxName
     *
     * @return string
     */
    protected static function exportTaxonomyFlatten(string $taxName): string
    {
        $output = 'CATEGORY;' . PHP_EOL;

        $terms = get_terms(
            $taxName,
            [
                'hide_empty' => false,
                'fields' => 'id=>name',
            ]
        );

        foreach ($terms as $term) {
            $output .= $term . ';' . PHP_EOL;
        }

        return PHP_EOL . $output;
    }

    /**
     * Exports taxonomy count.
     *
     * @param string $taxName
     *
     * @return string
     */
    public static function taxonomyCount(string $taxName): string
    {
        $terms = get_terms(
            $taxName,
            [
                'hide_empty' => false,
                'fields' => 'id=>name',
            ]
        );

        return is_array($terms) ? 'Total count: ' . count($terms) . PHP_EOL : 'Error: Unable to count!' . PHP_EOL;
    }

    /**
     * Exports 2- && 3- level taxonomy as a CSV text ready for import.
     *
     * @param string $taxName
     *
     * @return string
     * @fixme: Rewrite using recursion. To support any level of depth.
     */
    protected static function exportTaxonomyHierarchical(string $taxName): string
    {
        $output = '';

        $terms = get_terms(
            $taxName,
            [
                'hide_empty' => false,
                'fields' => 'id=>name',
            ]
        );
        $termsRelation = get_terms(
            $taxName,
            [
                'hide_empty' => false,
                'fields' => 'id=>parent',
            ]
        );

        foreach ($termsRelation as $categoryId => $parentId) {
            if ((int)$parentId === 0) {
                $subcategoriesWithParent = array_filter(
                    $termsRelation,
                    static fn($i) => (int)$i === (int)$categoryId
                );

                $output .= implode(
                    ";",
                    [
                        $terms[$categoryId],
                    ]
                ) . PHP_EOL;

                if (empty($subcategoriesWithParent)) {
                    continue;
                }

                foreach ($subcategoriesWithParent as $subcategoryId => $subcategoryParentId) {
                    $topicsWithParent = array_filter(
                        $termsRelation,
                        static fn($i) => (int)$i === (int)$subcategoryId
                    );
                    $topics = array_keys($topicsWithParent);

                    $output .= implode(
                        ";",
                        [
                            $terms[$categoryId],
                            $terms[$subcategoryId],
                        ]
                    ) . PHP_EOL;

                    if (empty($topics)) {
                        continue;
                    }

                    foreach ($topics as $topicId) {
                        $output .= implode(
                            ";",
                            [
                                $terms[$categoryId],
                                $terms[$subcategoryId],
                                $terms[$topicId],
                            ]
                        ) . PHP_EOL;
                    }
                }
            }
        }

        return PHP_EOL . $output;
    }

    /**
     * Strips '-' term's title prefix.
     *
     * @param string $name
     * @param bool $displayHierarchical
     *
     * @return string
     */
    public static function filterTermName(string $name, bool $displayHierarchical): string
    {
        // Leave default text value for taxonomy terms
        // if display_hierarchical flag is true
        return $displayHierarchical ? $name : str_replace('- ', '', $name);
    }

    /**
     * Get current php mode.
     *
     * @return boolean CLI mode.
     */
    public static function isCLI(): bool
    {
        return \defined('WP_CLI') && WP_CLI;
    }

    /**
     * Checks if it is a dev environment.
     *
     * @return bool
     */
    public static function isDev(): bool
    {
        return !self::isProd();
    }

    public static function isProd(): bool
    {
        return self::environment() === 'production';
    }

    /**
     * Import CSV file into array skipping the first row.
     *
     * @param string $fileName
     * @param string $separator
     *
     * @return array
     */
    public static function importCsv(string $fileName, string $separator = ';'): array
    {
        return array_filter(
            array_map(
                static fn(string $row) => str_getcsv($row, $separator),
                @file($fileName, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: []
            ),
            static fn(int $key) => $key !== 0,
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * Inserts term into taxonomy.
     *
     * @param string $term
     * @param string $taxonomy
     * @param string $parent
     * @param string $description
     */
    public static function insertTaxonomyTerm(
        string $term,
        string $taxonomy,
        string $parent = '',
        string $description = ''
    ): void {
        if (empty($term) || empty($taxonomy)) {
            return;
        }

        if (empty($description)) {
            $description = 'Please, update description for ' . $term . '.';
        }

        Logger::log(
            "Inserting term '$term' into taxonomy '$taxonomy' with parent '$parent'...",
            'info'
        );

        $existingTerm = get_term_by('name', $term, $taxonomy);
        $parentObject = null;
        if ($parent) {
            $parentObject = get_term_by('name', $parent, $taxonomy);
            // Normalize result.
            if (is_array($parentObject)) {
                $parentObject = array_shift($parentObject);
            }
        }

        $needsParentUpdate = false;

        if ($existingTerm instanceof WP_Term) {
            if (!($parentObject instanceof WP_Term)) {
                Logger::log("Term already exists: $existingTerm->term_id. Skipping.", 'success');

                return;
            }

            if ($existingTerm->parent === $parentObject->term_id) {
                Logger::log(
                    "Term already exists: $existingTerm->term_id with parent: $parentObject->term_id. Skipping.",
                    'success'
                );
                return;
            }
            $needsParentUpdate = true;
        }

        $args = $needsParentUpdate ? ['parent' => $parentObject->term_id] : [
            'description' => $description,
            'slug' => sanitize_title_with_dashes($term),
            'parent' => ($parentObject instanceof WP_Term) ? $parentObject->term_id : 0,
        ];

        $result = $needsParentUpdate ? wp_update_term(
            $existingTerm->term_id,
            $taxonomy,
            $args
        ) : wp_insert_term(
            $term,
            $taxonomy,
            $args
        );

        if (is_wp_error($result)) {
            Logger::log($result->get_error_message(), 'error');
        } else {
            $termId = $result['term_id'] ?? '';
            Logger::log(
                // phpcs:ignore
                $needsParentUpdate ? "Updated term ID: $termId with parent: $parentObject->term_id." : "Inserted term ID: $termId",
                'success'
            );
        }
    }

    /**
     * Get now datetime in DB ACF format.
     *
     * @return string
     * @throws Exception
     */
    public static function nowAcfDatetime(): string
    {
        // Date format: 2021-07-30 11:00:00
        return (new DateTime('now', new DateTimeZone(self::LEARNING_TIMEZONE)))
            ->format(self::ACF_DB_DATETIME_FORMAT);
    }

    /**
     * Get now datetime in DB ACF format.
     *
     * @param DateTime $date
     * @return string
     */
    public static function convertDateToAcfDateWithTimezone(DateTime $date): string
    {
        // Date format: 2021-07-30 11:00:00
        return $date->setTimezone(new DateTimeZone(Utils::LEARNING_TIMEZONE))
            ->format(Utils::ACF_DB_DATETIME_FORMAT);
    }

    /**
     * Recursively sort an array of taxonomy terms hierarchically. Child categories will be
     * placed under a 'children' member of their parent term.
     *
     * @param array $terms taxonomy term objects to sort
     * @param integer $parentId the current parent ID to put them in
     */
    public static function sortTermsHierarchically(array $terms, int $parentId = 0): array
    {
        $into = [];
        foreach ($terms as $term) {
            if ($term->parent === $parentId) {
                $term->children = self::sortTermsHierarchically($terms, $term->term_id);
                $into[$term->term_id] = $term;
            }
        }
        return $into;
    }

    /**
     * Create image attachment from a URL.
     *
     * @param string $url
     * @param string $description
     *
     * @return int
     * @throws Exception
     * @deprecated
     */
    public static function createFileAttachmentFromUrl(string $url, string $description = ''): int
    {
        return WpCore::mediaHandleSideload($url, $description);
    }

    /**
     * Check if current page belongs to CISO.
     * Supports only CISO landings currently.
     *
     * @param string $postPath
     * @param string $type
     *
     * @return bool
     */
    public static function isCisoPage(string $postPath, string $type): bool
    {
        // Currently, supports CISO landings only.
        return BlemmyaeApplications::isAppsLandingPath(
            BlemmyaeApplications::CISO,
            $postPath,
            $type
        );
    }

    /**
     * Return memory size into kb, mb etc.
     *
     * @return string
     */
    public static function memUsage(): string
    {
        $memoryLimit = ini_get('memory_limit');
        $memorySize = memory_get_usage();
        $memoryUnit = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB'];

        $memoryUsage = round(
            $memorySize / (1024 ** ($unitKey = floor(log($memorySize, 1024)))),
            2
        ) . ' ' . $memoryUnit[$unitKey];

        return 'Used Memory : ' . $memoryLimit . ' / ' . $memoryUsage . "\n";
    }

    /**
     * Switch between databases.
     *
     * @param string $newDB
     *
     * @return bool
     * @throws Exception
     */
    public function switchDB(string $newDB): bool
    {
        global $wpdb;

        // Store the old database name for reference.
        if ($wpdb->ludicrous_servers) {
            $this->defaultDB = $wpdb->used_servers['global__r']['name'];
        } else {
            $this->defaultDB = $wpdb->dbname;
        }

        // Check if the new database exists.
        $checkDB = $wpdb->get_var(
            $wpdb->prepare(
                'SELECT schema_name FROM information_schema.schemata WHERE schema_name = %s',
                $newDB
            )
        );

        if (!$checkDB) {
            throw new Exception("Unknown database $newDB.");
        }

        $wpdb->select($newDB);
        $wpdb->set_prefix($wpdb->base_prefix);

        if ($wpdb->check_connection(false)) {
            Logger::log("Switched from $this->defaultDB to $newDB.", 'success');
        } else {
            throw new Exception("Failed to switch from $this->defaultDB to $newDB.");
        }

        return true;
    }

    /**
     * Switch to default databases.
     *
     * @return void
     * @throws Exception
     */
    public function switchDBToDefault(): void
    {
        $this->switchDB($this->defaultDB);
    }

    /**
     * Deletes all terms for given taxonomy name.
     * @param string $taxonomyName
     * @return void
     */
    public static function deleteTermsAll(string $taxonomyName = ''): void
    {
        $terms = get_terms(array(
            'taxonomy' => $taxonomyName,
            'hide_empty' => false
        ));
        foreach ($terms as $term) {
            wp_delete_term($term->term_id, $taxonomyName);
        }
    }
}
