<?php

/**
 * @licence proprietary
 *
 * @author Konstantin Gusev <guvkon.net@icloud.com>
 */

declare(strict_types=1);

namespace Cra\Integrations\WebhookMessenger;

use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Throwable;

/**
 * Abstract message handler class for the webhook queue.
 *
 * @phpstan-ignore-next-line
 */
abstract class ConsumerMessageHandler implements MessageHandlerInterface
{
    private LoggerInterface $logger;

    /**
     * Class constructor.
     *
     * @param LoggerInterface $logger
     */
    final public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Handles WebhookMessage.
     *
     * @param ConsumerMessageInterface $message
     *
     * @throws Throwable
     */
    final public function __invoke(ConsumerMessageInterface $message): void
    {
        try {
            $this->processMessage($message);
        } catch (Throwable $exception) {
            $this->logger->warning(
                'Error processing webhook message - {exception}',
                ['exception' => $exception->getMessage()]
            );
            throw $exception;
        }
    }

    /**
     * Processes webhook message.
     *
     * @param ConsumerMessageInterface $message
     *
     * @return ProcessedMessageInterface
     * @throws Exception
     */
    protected function processMessage(ConsumerMessageInterface $message): ProcessedMessageInterface
    {
        $this->logger->debug('Processing webhook message: {message}', ['message' => $message]);
        $mapper = $this->mapperClass(
            $message->getObjectId()->getVendor(),
            $message->getObjectId()->getType(),
            $message->getObjectVersion()
        );
        if ($mapper->isObjectUptoDate($message->getObjectId(), $message->getTimestamp())) {
            $this->logger->debug('The object is up to date. Skipping.');

            return $mapper->getProcessedMessage($message, true);
        }

        switch ($message->getEvent()) {
            case 'create':
                $mapper->create($message->getObjectId(), $message->getTimestamp(), $message->getObject());
                break;
            case 'update':
                $mapper->update($message->getObjectId(), $message->getTimestamp(), $message->getObject());
                break;
            case 'delete':
                $mapper->delete($message->getObjectId(), $message->getTimestamp(), $message->getObject());
                break;
            default:
                throw new Exception("Unknown event type - {$message->getEvent()}");
        }
        $this->logger->debug('The object has been processed successfully.');

        return $mapper->getProcessedMessage($message, false);
    }

    /**
     * Get mapper class.
     *
     * @param string $vendor e.g. ppworks, csf
     * @param string $objectType
     * @param int|string $objectVersion
     *
     * @return ConsumerMapperInterface
     */
    abstract protected function mapperClass(
        string $vendor,
        string $objectType,
        int|string $objectVersion
    ): ConsumerMapperInterface;
}
