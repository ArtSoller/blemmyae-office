<?php

/**
 * Unit tests for ConsumerMessage
 *
 * @package      Cra\Integrations\WebhookMessenger\Tests\Unit
 * @author       Eugene Yakovenko
 * @copyright    2024 CRA
 * @license      proprietary
 */

declare(strict_types=1);

namespace Cra\Integrations\WebhookMessenger\Tests\Unit;

use Cra\Integrations\WebhookMessenger\ConsumerMessage;
use Cra\Integrations\WebhookMessenger\Tests\TestCase;

/**
 * ConsumerMappingFactory test case.
 */
class ConsumerMessageTest extends TestCase
{
    /**
     * Test constructor.
     */
    public function testConstructor(): void
    {
        $consumerMessage = new ConsumerMessage([
            'event' => 'update',
            'vendor' => 'test_vendor',
            'objectType' => 'test_type',
            'uuid' => '123456',
            'object' => (object)['type' => 'test'],
            'objectVersion' => null,
            'timestamp' => 987654321,
            'issuer' => 'blemmyae',
        ]);
        static::assertEquals('update', $consumerMessage->getEvent());
        static::assertEquals(987654321, $consumerMessage->getTimestamp());
    }
}
