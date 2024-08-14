<?php

/**
 * @licence proprietary
 *
 * @author Eugene Yakovenko <yakoveka@gmail.com>
 */

declare(strict_types=1);

namespace Cra\BlemmyaeDeployment\A9SMigration;

use Cra\Integrations\WebhookMessenger\ConsumerMappingTable;

/**
 * Entity class for migration mapping table.
 */
class MigrationMappingTable extends ConsumerMappingTable
{
    public function __construct()
    {
        $this->tableName = 'migration_mappings';
    }

    /**
     * @inheritDoc
     */
    public function getMappingsTable(): string
    {
        global $wpdb;

        return $wpdb->prefix . $this->tableName;
    }
}
