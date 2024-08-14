<?php

/**
 * @licence proprietary
 *
 * @author Konstantin Gusev <guvkon.net@icloud.com>
 */

/** @noinspection PhpUnused */

declare(strict_types=1);

namespace Cra\WebhookConsumer\Mapper;

use Cra\Integrations\WebhookMessenger\ConsumerMapperInterface;
use Cra\Integrations\WebhookMessenger\ConsumerMessageInterface;
use Cra\Integrations\WebhookMessenger\ConsumerObjectId;
use Cra\Integrations\WebhookMessenger\EntityId;
use Cra\Integrations\WebhookMessenger\ProcessedMessage;
use Cra\Integrations\WebhookMessenger\ProcessedMessageInterface;
use Cra\WebhookConsumer\WebhookMapping;
use Exception;
use Redirection_Admin;
use Scm\Tools\WpCore;

/**
 * Abstract webhook mapper class for WordPress entities.
 */
abstract class AbstractWordpressWebhookMapper implements ConsumerMapperInterface
{
    /**
     * @var array<string, WebhookMapping|null>
     */
    private array $webhookMappings = [];

    protected ConsumerObjectId $id;

    protected object $object;

    /**
     * Class constructor.
     */
    public function __construct()
    {
        if (defined('REDIRECTION_FILE')) {
            $redirectionPluginPath = plugin_dir_path(REDIRECTION_FILE);
            if ($redirectionPluginPath) {
                require_once $redirectionPluginPath . 'redirection-admin.php';
                /** @phpstan-ignore-next-line */
                Redirection_Admin::init();
            }
        }
    }

    /**
     * @inheritDoc
     */
    final public function isObjectUptoDate(ConsumerObjectId $id, int $timestamp): bool
    {
        $webhookMapping = $this->webhookMapping($id);
        if (!$webhookMapping) {
            return false;
        }

        return $webhookMapping->timestamp >= $timestamp;
    }

    /**
     * @inheritDoc
     */
    public function create(ConsumerObjectId $id, int $timestamp, object $object): void
    {
        $this->id = $id;
        $this->object = $object;
        $webhookMapping = $this->webhookMappingWithFallback($id);
        $entityId = $this->upsert($id, $timestamp, $object);
        $webhookMapping->postId = $entityId->id;
        $webhookMapping->entityType = $entityId->type;
        $webhookMapping->timestamp = $timestamp;
        $webhookMapping->object = $object;
        $webhookMapping->upsert();
        $this->webhookMappings[(string)$id] = $webhookMapping;
    }

    /**
     * @inheritDoc
     */
    final public function update(ConsumerObjectId $id, int $timestamp, object $object): void
    {
        $this->create($id, $timestamp, $object);
    }

    /**
     * @inheritDoc
     */
    final public function delete(ConsumerObjectId $id, int $timestamp, object $object): void
    {
        $this->id = $id;
        $this->object = $object;
        $webhookMapping = $this->webhookMappingWithFallback($id);
        $this->deleteWpEntity($webhookMapping);
        $webhookMapping->postId = 0;
        $webhookMapping->entityType = '';
        $webhookMapping->timestamp = $timestamp;
        $webhookMapping->object = $object;
        $webhookMapping->upsert();
        $this->webhookMappings[(string)$id] = $webhookMapping;
    }

    /**
     * @inheritDoc
     */
    public function getProcessedMessage(
        ConsumerMessageInterface $message,
        bool $isSkipped
    ): ProcessedMessageInterface {
        $webhookMapping = $this->webhookMapping($message->getObjectId());
        if (!$webhookMapping) {
            throw new Exception('Cannot find processed webhook mapping');
        }

        return new ProcessedMessage(
            $message,
            [
                'postId' => $message->getEvent() === 'delete' ?
                    'N/A' : (string)$webhookMapping->postId,
                'postType' => $this->wpEntityBundle(),
                'status' => $isSkipped ? 'skipped' : 'processed',
            ]
        );
    }

    /**
     * Get webhook mapping by webhook object ID.
     *
     * @param ConsumerObjectId $id Webhook object ID
     *
     * @return WebhookMapping|null
     */
    protected function webhookMapping(ConsumerObjectId $id): ?WebhookMapping
    {
        if (!array_key_exists((string)$id, $this->webhookMappings)) {
            $this->webhookMappings[(string)$id] = WebhookMapping::findById($id);
        }

        return $this->webhookMappings[(string)$id];
    }

    /**
     * Get webhook mapping by webhook object ID.
     *
     * If one doesn't exist create a fallback one from the ConsumerObjectId.
     *
     * @param ConsumerObjectId $id Webhook object ID
     *
     * @return WebhookMapping
     */
    protected function webhookMappingWithFallback(ConsumerObjectId $id): WebhookMapping
    {
        return $this->webhookMapping($id) ?? new WebhookMapping($id);
    }

    /**
     * @inheritDoc
     */
    abstract public function upsert(ConsumerObjectId $id, int $timestamp, object $object): EntityId;

    /**
     * @inheritDoc
     */
    abstract public function wpEntityBundle(): string;

    /**
     * Delete associated WordPress entity.
     *
     * @param WebhookMapping $mapping
     *
     * @return void
     * @throws Exception
     */
    protected function deleteWpEntity(WebhookMapping $mapping): void
    {
        if (empty($mapping->postId)) {
            return;
        }

        try {
            if (!$this->allowWpEntityDeletion()) {
                $this->doSoftEntityDeletion($mapping);

                return;
            }

            switch ($mapping->entityType) {
                case 'post':
                    WpCore::deletePost($mapping->postId, true);
                    break;
                case 'term':
                    WpCore::deleteTerm($mapping->postId, $this->wpEntityBundle());
                    break;
                default:
                    throw new Exception("Unexpected entity type value '$mapping->entityType'.");
            }
        } catch (Exception $exception) {
            $id = new ConsumerObjectId($mapping->vendor, $mapping->type, $mapping->id);
            throw new Exception("{$exception->getMessage()} - webhook object $id");
        }
    }

    /**
     * Allow deletion of the associated WordPress entity.
     *
     * @return bool
     */
    protected function allowWpEntityDeletion(): bool
    {
        return true;
    }

    /**
     * This is called when allowWpEntityDeletion is false.
     *
     * @param WebhookMapping $mapping Mapping here always has `postId` field set.
     *
     * @return void
     * @throws Exception
     */
    protected function doSoftEntityDeletion(WebhookMapping $mapping): void
    {
    }

    /**
     * Get vendor (external) ID.
     */
    protected function vendorId(): string|int
    {
        return $this->id->getId();
    }
}
