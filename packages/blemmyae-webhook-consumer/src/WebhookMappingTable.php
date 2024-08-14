<?php

/**
 * @licence proprietary
 *
 * @author Eugene Yakovenko <yakoveka@gmail.com>
 */

declare(strict_types=1);

namespace Cra\WebhookConsumer;

use Cra\Integrations\WebhookMessenger\ConsumerMappingTable;

/**
 * Entity class for webhook mapping table.
 */
class WebhookMappingTable extends ConsumerMappingTable
{
    public function __construct()
    {
        $this->tableName = 'webhook_mappings';
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
