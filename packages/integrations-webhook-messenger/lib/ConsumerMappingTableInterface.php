<?php

/**
 * @licence proprietary
 *
 * @author Eugene Yakovenko <yakoveka@gmail.com>
 */

declare(strict_types=1);

namespace Cra\Integrations\WebhookMessenger;

/**
 * Interfaces for webhook mapper classes which save webhook object to the target system.
 */
interface ConsumerMappingTableInterface
{
    /**
     * Set up mappings table.
     */
    public function setupMappingsTable(): void;

    /**
     * @return bool
     */
    public function isMappingsTableExist(): bool;

    /**
     * Add entity type to mappings table.
     *
     * @return void
     */
    public function addEntityTypeToMappingsTable(): void;
}
