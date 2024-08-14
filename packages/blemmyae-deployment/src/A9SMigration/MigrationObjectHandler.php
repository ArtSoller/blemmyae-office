<?php

/**
 * @licence proprietary
 *
 * @author Konstantin Gusev <guvkon.net@icloud.com>
 * @author  Alexander Kucherov <avdkucherov@gmail.com>
 */

namespace Cra\BlemmyaeDeployment\A9SMigration;

use Cra\Integrations\WebhookMessenger\ConsumerMapperInterface;
use Cra\Integrations\WebhookMessenger\ConsumerMessageInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Abstract message handler class for the webhook queue.
 */
abstract class MigrationObjectHandler
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
     * Handles MigrationObject.
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
     * @return ConsumerMessageInterface
     * @throws Exception
     */
    protected function processMessage(ConsumerMessageInterface $message): ConsumerMessageInterface
    {
        # @todo: Make optional.
        #$this->logger->debug('Processing webhook message: {message}', ['message' => $message]);
        $mapper = $this->mapperClass(
            $message->getObjectId()->getVendor(),
            $message->getObjectId()->getType()
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
     * @return ConsumerMapperInterface
     */
    abstract protected function mapperClass(string $vendor, string $objectType): ConsumerMapperInterface;
}
