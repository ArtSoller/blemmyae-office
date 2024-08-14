<?php

declare(strict_types=1);

namespace Cra\BlemmyaeDeployment;

/**
 * Class which setup new table in database with mappings old_entity_id => new_entity_id.
 */
class SetupMappingsTable
{
    /**
     * Setup new database table.
     *
     * @param string $tableName
     * @param string $oldValue
     * @param string $newValue
     * @return void
     */
    public function setupTable(string $tableName, string $oldValue, string $newValue): void
    {
        if (self::isMappingsTableExist($tableName)) {
            return;
        }

        global $wpdb;
        $sql = "CREATE TABLE $tableName (
                $oldValue bigint(20) NOT NULL PRIMARY KEY,
                $newValue bigint(20) NOT NULL
                ) {$wpdb->get_charset_collate()};";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public static function isMappingsTableExist(string $tableName): bool
    {
        global $wpdb;

        $result = $wpdb->query(
            $wpdb->prepare(
                'SHOW TABLES LIKE %s',
                $tableName
            )
        );

        return intval($result) === 1;
    }
}
