<?php

/**
 * Unit tests for ProcessedMessage
 *
 * @package      Cra\Integrations\WebhookMessenger\Tests\Unit
 * @author       Eugene Yakovenko
 * @copyright    2024 CRA
 * @license      proprietary
 */

declare(strict_types=1);

namespace Cra\Integrations\WebhookMessenger\Tests\Unit;

use Cra\Integrations\WebhookMessenger\ConsumerMessage;
use Cra\Integrations\WebhookMessenger\ProcessedMessage;
use Cra\Integrations\WebhookMessenger\Tests\TestCase;

/**
 * ProcessedMessage test case.
 */
class ProcessedMessageTest extends TestCase
{
    /**
     * A single example test.
     */
    public function testProcessedMessage(): void
    {
        $message = new ConsumerMessage(
            [
                'event' => 'update',
                'vendor' => 'test_vendor',
                'objectType' => 'test_type',
                'uuid' => '123456',
                'object' => (object) ['type' => 'test'],
                'objectVersion' => null,
                'timestamp' => 987654321,
                'issuer' => 'blemmyae',
            ],
        );
        $webhookProduct = new ProcessedMessage($message, [
            'postId' => '123456',
            'postType' => 'editorial',
            'status' => 'processed',
        ]);
        static::assertEquals('processed', $webhookProduct->getStatus());
        static::assertEquals('123456', $webhookProduct->getPostId());
    }
}
