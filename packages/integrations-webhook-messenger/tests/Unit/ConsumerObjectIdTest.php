<?php

/**
 * Unit tests for ConsumerObjectId
 *
 * @package      Cra\Integrations\WebhookMessenger\Tests\Unit
 * @author       Eugene Yakovenko
 * @copyright    2024 CRA
 * @license      proprietary
 */

declare(strict_types=1);

namespace Cra\Integrations\WebhookMessenger\Tests\Unit;

use Cra\Integrations\WebhookMessenger\ConsumerObjectId;
use Cra\Integrations\WebhookMessenger\Tests\TestCase;

/**
 * ConsumerObjectId test case.
 */
class ConsumerObjectIdTest extends TestCase
{
    /**
     * Test constructor.
     */
    public function testConstructor(): void
    {
        $consumerObjectId = new ConsumerObjectId('test_vendor', 'test_type', '123456');
        static::assertEquals('123456', $consumerObjectId->getId());
        static::assertEquals('test_vendor', $consumerObjectId->getVendor());
        static::assertEquals('test_type', $consumerObjectId->getType());
    }
}
