<?php

namespace Cra\WebhookConsumer;

use WP_Stream\Connector;

class BlemmyaeWebhookConsumerStreamConnector extends Connector
{
    /**
     * Connector slug
     *
     * @var string
     */
    // phpcs:ignore
    public $name = 'webhook_consumer';

    /**
     * Actions registered for this connector
     *
     * Tracking webhook message events and webhook mapping deletion
     *
     * @var array
     */
    // phpcs:ignore
    public $actions = [
        self::ACTION_WEBHOOK_MESSAGE_CONSUMED,
        self::ACTION_WEBHOOK_MESSAGE_MAPPING_CREATED,
        self::ACTION_WEBHOOK_MESSAGE_MAPPING_UPDATED,
        self::ACTION_WEBHOOK_MESSAGE_POST_CREATED,
        self::ACTION_WEBHOOK_MESSAGE_POST_UPDATED,
        self::ACTION_WEBHOOK_MESSAGE_TERM_CREATED,
        self::ACTION_WEBHOOK_MESSAGE_TERM_UPDATED,
        self::ACTION_WEBHOOK_MESSAGE_ACKNOWLEDGED,
    ];

    // Duplicate code here because there is no access to class instance
    public static array $trackedActions = [
        self::ACTION_WEBHOOK_MESSAGE_CONSUMED,
        self::ACTION_WEBHOOK_MESSAGE_MAPPING_CREATED,
        self::ACTION_WEBHOOK_MESSAGE_MAPPING_UPDATED,
        self::ACTION_WEBHOOK_MESSAGE_POST_CREATED,
        self::ACTION_WEBHOOK_MESSAGE_POST_UPDATED,
        self::ACTION_WEBHOOK_MESSAGE_TERM_CREATED,
        self::ACTION_WEBHOOK_MESSAGE_TERM_UPDATED,
        self::ACTION_WEBHOOK_MESSAGE_ACKNOWLEDGED,
    ];

    private const CONTEXT_WEBHOOK_MAPPINGS = 'webhook_mappings';
    private const CONTEXT_POSTS = 'posts';
    private const CONTEXT_TERMS = 'terms';
    private const CONTEXT_WEBHOOK_MESSAGE = 'webhook_message';

    public const ACTION_WEBHOOK_MESSAGE_CONSUMED = 'webhook_message_consumed';
    public const ACTION_WEBHOOK_MESSAGE_MAPPING_CREATED = 'webhook_message_mapping_created';
    public const ACTION_WEBHOOK_MESSAGE_MAPPING_UPDATED = 'webhook_message_mapping_updated';
    public const ACTION_WEBHOOK_MESSAGE_POST_CREATED = 'webhook_message_post_created';
    public const ACTION_WEBHOOK_MESSAGE_POST_UPDATED = 'webhook_message_post_updated';
    public const ACTION_WEBHOOK_MESSAGE_TERM_CREATED = 'webhook_message_term_created';
    public const ACTION_WEBHOOK_MESSAGE_TERM_UPDATED = 'webhook_message_term_updated';
    public const ACTION_WEBHOOK_MESSAGE_ACKNOWLEDGED = 'webhook_message_acknowledged';

    private static array $actionToTemplate = [
        self::ACTION_WEBHOOK_MESSAGE_CONSUMED =>
            "Message consumed - %s",
        self::ACTION_WEBHOOK_MESSAGE_MAPPING_CREATED =>
            "Created %s webhook mapping, post_id - %s",
        self::ACTION_WEBHOOK_MESSAGE_MAPPING_UPDATED =>
            "Updated %s webhook mapping, post_id - %s",
        self::ACTION_WEBHOOK_MESSAGE_POST_CREATED =>
            "Created post, %s webhook mapping, post_id - %s",
        self::ACTION_WEBHOOK_MESSAGE_POST_UPDATED =>
            "Updated post, %s webhook mapping, post_id - %s",
        self::ACTION_WEBHOOK_MESSAGE_TERM_CREATED =>
            "Created term, %s webhook mapping, term_id - %s",
        self::ACTION_WEBHOOK_MESSAGE_TERM_UPDATED =>
            "Updated term, %s webhook mapping, term_id - %s",
        self::ACTION_WEBHOOK_MESSAGE_ACKNOWLEDGED =>
            "Message acknowledged - %s",
    ];

    /**
     * Register wp actions
     *
     * @return void
     */
    public static function addActions(): void
    {
        foreach (self::$trackedActions as $action) {
            add_action($action, fn($webhookId, $postId) => null, 10, 2);
        }
    }

    /**
     * Return translated connector label
     *
     * @return string
     */
    // phpcs:ignore
    public function get_label(): string
    {
        return __('Webhook Consumer', 'webhook-consumer');
    }

    /**
     * Return translated context labels
     *
     * @return array
     */
    // phpcs:ignore
    public function get_context_labels(): array
    {
        return [
            self::CONTEXT_WEBHOOK_MAPPINGS => __('Webhook Mappings', 'webhook-consumer'),
            self::CONTEXT_POSTS => __('Posts', 'webhook-consumer'),
            self::CONTEXT_TERMS => __('Terms', 'webhook-consumer'),
            self::CONTEXT_WEBHOOK_MESSAGE => __('Webhook Messages', 'webhook-consumer'),
        ];
    }

    /**
     * Return translated action labels
     *
     * @return array
     */
    // phpcs:ignore
    public function get_action_labels(): array
    {
        return [
            self::ACTION_WEBHOOK_MESSAGE_CONSUMED =>
                __('Message consumed', 'webhook-consumer'),
            self::ACTION_WEBHOOK_MESSAGE_MAPPING_CREATED =>
                __('Mapping created', 'webhook-consumer'),
            self::ACTION_WEBHOOK_MESSAGE_MAPPING_UPDATED =>
                __('Mapping updated', 'webhook-consumer'),
            self::ACTION_WEBHOOK_MESSAGE_POST_CREATED =>
                __('Post created', 'webhook-consumer'),
            self::ACTION_WEBHOOK_MESSAGE_POST_UPDATED =>
                __('Post updated', 'webhook-consumer'),
            self::ACTION_WEBHOOK_MESSAGE_TERM_CREATED =>
                __('Term created', 'webhook-consumer'),
            self::ACTION_WEBHOOK_MESSAGE_TERM_UPDATED =>
                __('Term updated', 'webhook-consumer'),
            self::ACTION_WEBHOOK_MESSAGE_ACKNOWLEDGED =>
                __('Message acknowledged', 'webhook-consumer'),
        ];
    }

    /**
     * Generate Stream log message
     *
     * @param $action
     * @param $context
     * @param ...$args
     *
     * @return void
     */
    private function webhookMappingLog($action, $context, ...$args): void
    {
        $message = sprintf(self::$actionToTemplate[$action], ...$args);

        /** @phpstan-ignore-next-line */
        $this->log(
            $message,
            [
                'action' => $action,
                'id' => $args[0] ?? '0',
                'title' => $message,
            ],
            $args[0] ?? '0',
            $context,
            $action
        );
    }

    /**
     * Callback for webhook_message_consumed action
     *
     * @param $mappingId
     * @param $postId
     *
     * @return void
     */
    // phpcs:ignore
    public function callback_webhook_message_consumed($mappingId, $postId): void
    {
        $this->webhookMappingLog(
            self::ACTION_WEBHOOK_MESSAGE_CONSUMED,
            self::CONTEXT_WEBHOOK_MESSAGE,
            $mappingId,
            $postId
        );
    }

    /**
     * Callback for webhook_message_mapping_created action
     *
     * @param $mappingId
     * @param $postId
     *
     * @return void
     */
    // phpcs:ignore
    public function callback_webhook_message_mapping_created($mappingId, $postId): void
    {
        $this->webhookMappingLog(
            self::ACTION_WEBHOOK_MESSAGE_MAPPING_CREATED,
            self::CONTEXT_WEBHOOK_MESSAGE,
            $mappingId,
            $postId
        );
        $this->webhookMappingLog(
            self::ACTION_WEBHOOK_MESSAGE_MAPPING_CREATED,
            self::CONTEXT_WEBHOOK_MAPPINGS,
            $mappingId,
            $postId
        );
    }

    /**
     * Callback for webhook_message_mapping_updated action
     *
     * @param $mappingId
     * @param $postId
     *
     * @return void
     */
    // phpcs:ignore
    public function callback_webhook_message_mapping_updated($mappingId, $postId): void
    {
        $this->webhookMappingLog(
            self::ACTION_WEBHOOK_MESSAGE_MAPPING_UPDATED,
            self::CONTEXT_WEBHOOK_MESSAGE,
            $mappingId,
            $postId
        );
        $this->webhookMappingLog(
            self::ACTION_WEBHOOK_MESSAGE_MAPPING_UPDATED,
            self::CONTEXT_WEBHOOK_MAPPINGS,
            $mappingId,
            $postId
        );
    }

    /**
     * Callback for webhook_message_post_created action
     *
     * @param $mappingId
     * @param $postId
     *
     * @return void
     */
    // phpcs:ignore
    public function callback_webhook_message_post_created($mappingId, $postId): void
    {
        $this->webhookMappingLog(
            self::ACTION_WEBHOOK_MESSAGE_POST_CREATED,
            self::CONTEXT_WEBHOOK_MAPPINGS,
            $mappingId,
            $postId
        );
        $this->webhookMappingLog(self::ACTION_WEBHOOK_MESSAGE_POST_CREATED, self::CONTEXT_POSTS, $mappingId, $postId);
    }

    /**
     * Callback for webhook_message_post_updated action
     *
     * @param $mappingId
     * @param $postId
     *
     * @return void
     */
    // phpcs:ignore
    public function callback_webhook_message_post_updated($mappingId, $postId): void
    {
        $this->webhookMappingLog(
            self::ACTION_WEBHOOK_MESSAGE_POST_UPDATED,
            self::CONTEXT_WEBHOOK_MESSAGE,
            $mappingId,
            $postId
        );
        $this->webhookMappingLog(self::ACTION_WEBHOOK_MESSAGE_POST_UPDATED, self::CONTEXT_POSTS, $mappingId, $postId);
    }

    /**
     * Callback for webhook_message_term_created action
     *
     * @param $mappingId
     * @param $postId
     *
     * @return void
     */
    // phpcs:ignore
    public function callback_webhook_message_term_created($mappingId, $postId): void
    {
        $this->webhookMappingLog(
            self::ACTION_WEBHOOK_MESSAGE_TERM_CREATED,
            self::CONTEXT_WEBHOOK_MESSAGE,
            $mappingId,
            $postId
        );
        $this->webhookMappingLog(self::ACTION_WEBHOOK_MESSAGE_TERM_CREATED, self::CONTEXT_TERMS, $mappingId, $postId);
    }

    /**
     * Callback for webhook_message_term_updated action
     *
     * @param $mappingId
     * @param $postId
     *
     * @return void
     */
    // phpcs:ignore
    public function callback_webhook_message_term_updated($mappingId, $postId): void
    {
        $this->webhookMappingLog(
            self::ACTION_WEBHOOK_MESSAGE_TERM_UPDATED,
            self::CONTEXT_WEBHOOK_MESSAGE,
            $mappingId,
            $postId
        );
        $this->webhookMappingLog(self::ACTION_WEBHOOK_MESSAGE_TERM_UPDATED, self::CONTEXT_TERMS, $mappingId, $postId);
    }

    /**
     * Callback for webhook_message_acknowledged action
     *
     * @param $mappingId
     * @param $postId
     *
     * @return void
     */
    // phpcs:ignore
    public function callback_webhook_message_acknowledged($mappingId, $postId): void
    {
        $this->webhookMappingLog(
            self::ACTION_WEBHOOK_MESSAGE_ACKNOWLEDGED,
            self::CONTEXT_WEBHOOK_MESSAGE,
            $mappingId,
            $postId
        );
    }
}
