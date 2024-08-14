<?php

/**
 * @author  Konstantin Gusev <guvkon.net@icloud.com>
 * @license proprietary
 */

declare(strict_types=1);

namespace Cra\Integrations\WebhookMessenger;

/**
 * Data class which uniquely identifies WordPress entity in webhook_mapping table.
 */
final class EntityId
{
    public int $id;

    public string $type;

    public function __construct(int $id, string $type)
    {
        $this->id = $id;
        $this->type = $type;
    }
}
