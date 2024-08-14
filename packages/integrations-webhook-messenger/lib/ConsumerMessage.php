<?php

/**
 * @licence proprietary
 *
 * @author Eugene Yakovenko <yakoveka@gmail.com>
 */

declare(strict_types=1);

namespace Cra\Integrations\WebhookMessenger;

/**
 * Message class for the webhook queue.
 */
class ConsumerMessage implements ConsumerMessageInterface
{
    use ValidatorTrait;
    use ConsumerMessageTrait;

    /**
     * Construct ConsumerMessage from array $message.
     *
     * @param array<string,mixed> $message
     */
    public function __construct(array $message)
    {
        $this->setEvent($message['event']);
        $this->setObjectId(new ConsumerObjectId(
            $message['vendor'] ?? '',
            $message['objectType'] ?? '',
            $message['uuid'] ?? ''
        ));
        $this->setObject($message['object']);
        $this->setTimestamp($message['timestamp']);
    }
}
