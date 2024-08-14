<?php

/**
 * @link https://cra.myjetbrains.com/youtrack/issue/PORT-1818
 *
 * @author  Konstantin Gusev <guvkon.net@icloud.com>
 * @license proprietary
 */

declare(strict_types=1);

use Cra\WebhookConsumer\Mapper\Vendor\Ppworks\Sponsor;
use Cra\WebhookConsumer\WebhookMapping;
use Cra\WebhookConsumer\WebhookMessageHandler;
use Scm\Tools\Logger;

/**
 * Drop all ppworks sponsor associations.
 */
function port1818_blemmyae_webhook_consumer_ri_001(): void
{
    try {
        WebhookMapping::deleteByVendorAndType(WebhookMessageHandler::VENDOR__PPWORKS, Sponsor::TYPE);
    } catch (Exception $exception) {
        Logger::log($exception->getMessage(), 'warning');
    }
}
