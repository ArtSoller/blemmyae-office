<?php

/**
 * @link https://cra.myjetbrains.com/youtrack/issue/SW-463
 *
 * @author  Konstantin Gusev <guvkon.net@icloud.com>
 * @license proprietary
 */

declare(strict_types=1);

use Cra\WebhookConsumer\Mapper\Vendor\Ppworks\Category;
use Cra\WebhookConsumer\WebhookMapping;
use Cra\WebhookConsumer\WebhookMessageHandler;
use Scm\Tools\Logger;

/**
 * Drop all ppworks category associations.
 */
function sw463_blemmyae_webhook_consumer_ri_001(): void
{
    try {
        WebhookMapping::deleteByVendorAndType(WebhookMessageHandler::VENDOR__PPWORKS, Category::TYPE);
    } catch (Exception $exception) {
        Logger::log($exception->getMessage(), 'warning');
    }
}
