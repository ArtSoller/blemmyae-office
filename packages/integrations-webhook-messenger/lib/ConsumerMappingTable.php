<?php

/**
 * @licence proprietary
 *
 * @author Eugene Yakovenko <yakoveka@gmail.com>
 */

declare(strict_types=1);

namespace Cra\Integrations\WebhookMessenger;

/**
 * Entity class for consumer mapping table.
 */
abstract class ConsumerMappingTable implements ConsumerMappingTableInterface
{
    public string $tableName;

    /**
     * @inheritDoc
     */
    public function setupMappingsTable(): void
    {
        if ($this->isMappingsTableExist()) {
            return;
        }

        global $wpdb;

        $table = $this->getMappingsTable();

        $wpdb->query(
            "CREATE TABLE $table (
            `post_id` BIGINT(20) UNSIGNED NOT NULL,
            `vendor` VARCHAR(10) NOT NULL,
            `type` VARCHAR(16) NOT NULL,
            `id` VARCHAR(36) NOT NULL,
            `timestamp` BIGINT(20) UNSIGNED NOT NULL,
            `object` BLOB,
            KEY `post_id` (`post_id`),
            UNIQUE KEY `vendor_type_id` (`vendor`,`type`,`id`)
        ) {$wpdb->get_charset_collate()};"
        );
    }

    /**
     * @inheritDoc
     */
    public function addEntityTypeToMappingsTable(): void
    {
        global $wpdb;

        $table = $this->getMappingsTable();

        $wpdb->query(
            "ALTER TABLE $table ADD COLUMN entity_type VARCHAR(4) NOT NULL DEFAULT '',
            ADD INDEX `entity_id_type` (`post_id`,`entity_type`);"
        );
        $wpdb->query("UPDATE $table SET `entity_type` = 'post' WHERE post_id > 0;");
        $wpdb->query(
            "UPDATE $table SET `entity_type` = 'term' WHERE post_id > 0
             AND `vendor` = 'ppworks' AND `type` IN ('category', 'show', 'tag');"
        );
    }

    /**
     * @inheritDoc
     */
    public function isMappingsTableExist(): bool
    {
        global $wpdb;

        $result = $wpdb->query(
            $wpdb->prepare(
                'SHOW TABLES LIKE %s',
                $this->getMappingsTable()
            )
        );

        return intval($result) === 1;
    }

    /**
     * Get full webhook_mapper table name (including prefix).
     *
     * @return string
     */
    abstract public function getMappingsTable(): string;
}
