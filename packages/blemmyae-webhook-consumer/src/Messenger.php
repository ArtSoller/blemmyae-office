<?php

/**
 * @licence proprietary
 *
 * @author Konstantin Gusev <guvkon.net@icloud.com>
 */

declare(strict_types=1);

namespace Cra\WebhookConsumer;

use Cra\Integrations\WebhookMessenger\WebhookMessage;
use Symfony\Component\Messenger\Handler\HandlersLocator;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;

/**
 * Class which inits Symfony Messenger bus.
 */
class Messenger
{
    private static self $instance;

    private MessageBus $bus;

    /**
     * Get singleton instance.
     *
     * @return static
     */
    public static function instance(): self
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Enforce singleton.
     */
    private function __construct()
    {
    }

    /**
     * Get messenger bus.
     *
     * @return MessageBus
     */
    public function bus(): MessageBus
    {
        if (!isset($this->bus)) {
            $this->bus = new MessageBus([
                new HandleMessageMiddleware(
                    new HandlersLocator($this->handlers())
                ),
            ]);
        }

        return $this->bus;
    }

    /**
     * Get list of message => message handlers.
     *
     * @return array
     */
    protected function handlers(): array
    {
        $handlers = [
            WebhookMessage::class => [new WebhookMessageHandler(new Logger())],
        ];

        return apply_filters('symfony_messenger_handlers', $handlers);
    }
}
