<?php

/**
 * @licence proprietary
 *
 * @author Konstantin Gusev <guvkon.net@icloud.com>
 */

declare(strict_types=1);

namespace Cra\WebhookConsumer;

use Cra\Integrations\WebhookMessenger\ConsumerObjectId;
use Cra\Integrations\WebhookMessenger\TtlStamp;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Bridge\AmazonSqs\Transport\AmazonSqsTransportFactory;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\TransportException;
use Symfony\Component\Messenger\Transport\Serialization\PhpSerializer;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Throwable;

/**
 * Class for handling webhook messages.
 */
class Webhook
{
    private const LOCK_OPTION = 'webhook_consumer_lock';

    private LoggerInterface $logger;

    private TransportInterface $incomingTransport;

    private TransportInterface $failedTransport;

    /**
     * Is debug mode on?
     *
     * @return bool
     */
    public static function isDebug(): bool
    {
        return defined('WEBHOOK_MESSAGE_DEBUG') && WEBHOOK_MESSAGE_DEBUG;
    }

    /**
     * Get Amazon SQS transport instance.
     *
     * @param string $queue
     * @param int $bufferSize
     * @param bool $debug
     *
     * @return TransportInterface
     */
    public static function sqsTransport(string $queue, int $bufferSize = 9, bool $debug = false): TransportInterface
    {
        // The maximum value of the buffer size is 9 for SQS.
        $config = self::sqsConfig($queue, min(9, $bufferSize), $debug);

        return (new AmazonSqsTransportFactory(new Logger()))->createTransport(
            self::dsnFromConfig($config),
            $config,
            new PhpSerializer()
        );
    }

    /**
     * Get Amazon SQS config for a queue.
     *
     * @param string $queue
     * @param int $bufferSize
     * @param bool $debug
     *
     * @return array
     */
    private static function sqsConfig(string $queue, int $bufferSize = 9, bool $debug = false): array
    {
        return [
            'access_key' => SQS_INTEGRATIONS_ACCESS_KEY,
            'secret_key' => SQS_INTEGRATIONS_SECRET_KEY,
            'endpoint' => SQS_INTEGRATIONS_ENDPOINT,
            'region' => SQS_INTEGRATIONS_REGION,
            'account' => SQS_INTEGRATIONS_ACCOUNT,
            'queue_name' => $queue,
            'auto_setup' => false,
            'buffer_size' => $bufferSize,
            'visibility_timeout' => self::visibilityTimeout(),
            'debug' => $debug,
            'poll_timeout' => defined('SQS_INTEGRATIONS_POLL_TIMEOUT') ?
                (SQS_INTEGRATIONS_POLL_TIMEOUT ?: 2) : 2,
        ];
    }

    private static function visibilityTimeout(): int
    {
        $default = 900;
        $visibility = defined('WEBHOOK_VISIBILITY_TIMEOUT') ?
            intval(WEBHOOK_VISIBILITY_TIMEOUT) : $default;

        return $visibility ?: $default;
    }

    /**
     * Get DSN string from SQS config.
     *
     * @param array $config
     *
     * @return string
     */
    private static function dsnFromConfig(array $config): string
    {
        // Format: {ENDPOINT}/{ACCOUNT}/{QUEUE}?access_key={ACCESS_KEY}&secret_key={SECRET_KEY}
        $url = implode('/', [$config['endpoint'], $config['account'], $config['queue_name']]);
        $url .= '?' . http_build_query([
            'access_key' => $config['access_key'],
            'secret_key' => $config['secret_key'],
        ]);

        return $url;
    }

    /**
     * Class constructor.
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Handle webhook messages.
     *
     * @param int $bufferSize Number of messages to handle.
     */
    public function handleMessages(int $bufferSize): void
    {
        if (
            !defined('SQS_INTEGRATIONS_ACCESS_KEY') ||
            !defined('SQS_INTEGRATIONS_SECRET_KEY') ||
            !defined('SQS_INTEGRATIONS_ENDPOINT') ||
            !defined('SQS_INTEGRATIONS_REGION') ||
            !defined('SQS_INTEGRATIONS_ACCOUNT') ||
            !defined('SQS_INTEGRATIONS_INCOMING_QUEUE') ||
            !defined('SQS_INTEGRATIONS_PROCESSED_QUEUE') ||
            !defined('SQS_INTEGRATIONS_FAILED_QUEUE')
        ) {
            $this->logger->notice('Missing SQS configs for the webhook queues!');
            return;
        }

        // Freeze time.
        $startTime = time();

        $this->logger->debug(
            'Started handleMessages' . json_encode([
                'bufferSize' => $bufferSize,
                'visibilityTimeout' => self::visibilityTimeout(),
                'startTime' => $startTime,
            ])
        );

        // We cannot process messages out of order so need to check if it's being processed now.
        if ($this->isLocked($startTime)) {
            $this->logger->debug('Lock is present. Stopping.');
            return;
        }

        $this->lock($startTime);

        // We cannot afford stopping processing messages until they are done.
        // There is going to be a manual check if the process should stop.
        set_time_limit(0);

        $this->incomingTransport = self::sqsTransport(SQS_INTEGRATIONS_INCOMING_QUEUE, $bufferSize);
        $this->failedTransport = self::sqsTransport(SQS_INTEGRATIONS_FAILED_QUEUE);
        for ($index = 0; $index < $bufferSize; $index++) {
            if (!$this->processMessage()) {
                // There are no messages left in the queue or errors acknowledging.
                break;
            }
            if ((time() - $startTime) > self::visibilityTimeout()) {
                $this->logger->debug('We have been processing too long. Stopping.');
                $index++; // break does not increment $index.
                break;
            }
        }
        $this->unlock();
        $this->logger->debug("Processed $index webhook messages.");
    }

    /**
     * Is processing of the webhook queue is locked.
     *
     * @param int $now
     *
     * @return bool
     */
    private function isLocked(int $now): bool
    {
        $lock = (int)get_option(self::LOCK_OPTION, 0);
        if ($lock === 0) {
            return false;
        }

        return ($now - $lock) < $this->lockTimeout();
    }

    /**
     * Get lock timeout.
     *
     * @return int
     */
    private function lockTimeout(): int
    {
        return self::visibilityTimeout();
    }

    /**
     * Lock processing of the webhook queue.
     *
     * @param int $now
     *
     * @return void
     */
    private function lock(int $now): void
    {
        update_option(self::LOCK_OPTION, $now);
    }

    /**
     * Unlock processing of the webhook queue.
     *
     * @return void
     */
    private function unlock(): void
    {
        delete_option(self::LOCK_OPTION);
    }

    /**
     * Create a string key of webhook object
     *
     * @param ConsumerObjectId $messageObjectId
     *
     * @return string
     */
    public static function messageKey(ConsumerObjectId $messageObjectId): string
    {
        return implode('-', [
            $messageObjectId->getVendor(),
            $messageObjectId->getType(),
            $messageObjectId->getId(),
        ]);
    }

    /**
     * Process a message from the incoming messages transport.
     *
     * @return bool Returns true if a message has been processed.
     */
    private function processMessage(): bool
    {
        $isMessageProcessed = false;
        // SQS transport can only return a single message per get() method
        // but get() still returns an iterable.
        foreach ($this->incomingTransport->get() as $envelope) {
            $message = $envelope->getMessage();
            $messageObjectId = $message->getObjectId();
            do_action(
                BlemmyaeWebhookConsumerStreamConnector::ACTION_WEBHOOK_MESSAGE_CONSUMED,
                self::messageKey($messageObjectId),
                ''
            );
            try {
                $this->doProcessMessage($message);
            } catch (Throwable $exception) {
                $this->handleException($envelope, $exception);
            }
            try {
                $this->incomingTransport->ack($envelope);
                do_action(
                    BlemmyaeWebhookConsumerStreamConnector::ACTION_WEBHOOK_MESSAGE_ACKNOWLEDGED,
                    self::messageKey($messageObjectId),
                    ''
                );
            } catch (TransportException $exception) {
                $this->logger->warning("Error acknowledging message = {$exception->getMessage()}");
                return false;
            }
            $isMessageProcessed = true;
        }

        return $isMessageProcessed;
    }

    /**
     * Just process message without error handling.
     *
     * @param object $message
     *
     * @return void
     * @throws Throwable
     */
    private function doProcessMessage(object $message): void
    {
        $this->logger->debug('Message has been dispatched.');
        Messenger::instance()->bus()->dispatch($message);
        $this->logger->debug('Message has been processed.');
    }

    /**
     * Handle exception thrown during message processing.
     *
     * @param Envelope $envelope
     * @param Throwable $exception
     *
     * @return void
     */
    private function handleException(Envelope $envelope, Throwable $exception): void
    {
        $this->logger->warning($exception->getMessage());

        /** @var TtlStamp[] $stamps */
        $stamps = $envelope->all(TtlStamp::class) ?: [new TtlStamp()];
        $isProcessable = array_reduce(
            $stamps,
            static fn(bool $carry, TtlStamp $stamp) => $carry && $stamp->decrease()->isProcessable(),
            true
        );

        if ($isProcessable) {
            $this->incomingTransport->send(Envelope::wrap($envelope->getMessage(), $stamps));
        } else {
            $this->failedTransport->send(Envelope::wrap($envelope->getMessage()));
        }
    }
}
