<?php

/**
 * Main plugin file
 *
 * @package   Cra\WebhookConsumer
 * @author    Squiz Pty Ltd <products@squiz.net>
 * @copyright 2021 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   proprietary
 */

declare(strict_types=1);

namespace Cra\WebhookConsumer;

use BrightNucleus\Config\ConfigInterface;
use BrightNucleus\Config\ConfigTrait;
use BrightNucleus\Config\Exception\FailedToProcessConfigException;
use BrightNucleus\Settings\Settings;
use Cra\WebhookConsumer\Command\Webhook as WebhookCommand;
use Exception;
use Psr\Log\LoggerInterface;
use Scm\Tools\Utils;
use WP_CLI;

/**
 * Main plugin class.
 *
 * @since 0.1.0
 *
 * @package Cra\WebhookConsumer
 * @author  Gary Jones
 */
class Plugin
{
    use ConfigTrait;

    private const WEBHOOK_MESSAGE_CRON_INTERVAL = 'webhook_message';

    private const WEBHOOK_MESSAGE_CRON = 'webhook_message_cron';

    private const PLUGIN_VERSION_OPTION = 'webhook_consumer_plugin_version';

    private LoggerInterface $logger;

    /**
     * Static instance of the plugin.
     *
     * @since 0.1.0
     *
     * @var self
     */
    protected static Plugin $instance;

    /**
     * Instantiate a Plugin object.
     *
     * Don't call the constructor directly, use the `Plugin::get_instance()`
     * static method instead.
     *
     * @param ConfigInterface $config Config to parametrize the object.
     *
     * @throws FailedToProcessConfigException If the Config could not be parsed correctly.
     *
     * @since 0.1.0
     *
     */
    public function __construct(ConfigInterface $config)
    {
        $this->logger = new Logger();
        $this->processConfig($config);
        require_once(ABSPATH . "wp-content/plugins/stream/classes/class-connector.php");
        BlemmyaeWebhookConsumerStreamConnector::addActions();
    }

    /**
     * Launch the initialization process.
     *
     * @throws Exception
     * @since 0.1.0
     */
    public function run(): void
    {
        add_action('plugins_loaded', [$this, 'loadTextDomain']);

        add_filter('cron_schedules', [$this, 'webhookMessageInterval']);
        add_action(self::WEBHOOK_MESSAGE_CRON, [$this, 'processWebhookMessages']);
        add_action('deleted_post', [$this, 'onDeletedPost']);
        add_action('deleted_term_taxonomy', [$this, 'onDeletedTerm']);

        add_filter('wp_stream_connectors', static function ($classes) {
            $webhookConsumerConnector = new BlemmyaeWebhookConsumerStreamConnector();
            $classes [] = $webhookConsumerConnector;
            return $classes;
        });

        if (!wp_next_scheduled(self::WEBHOOK_MESSAGE_CRON)) {
            wp_schedule_event(time(), self::WEBHOOK_MESSAGE_CRON_INTERVAL, self::WEBHOOK_MESSAGE_CRON);
        }

        $pluginVersion = get_option(self::PLUGIN_VERSION_OPTION, '0.1');
        $mappingTable = new WebhookMappingTable();
        if (version_compare($pluginVersion, '1.1') < 0) {
            add_action('init', static function () use ($mappingTable) {
                $mappingTable->setupMappingsTable();
                update_option(Plugin::PLUGIN_VERSION_OPTION, '1.1');
            });
        }
        if (version_compare($pluginVersion, '1.2') < 0) {
            add_action('init', static function () use ($mappingTable) {
                $mappingTable->addEntityTypeToMappingsTable();
                update_option(Plugin::PLUGIN_VERSION_OPTION, '1.2');
            });
        }

        if (Utils::isCLI()) {
            WP_CLI::add_command('webhook', WebhookCommand::class);
        }
    }

    /**
     * Load the plugin text domain.
     *
     * @since 0.1.0
     */
    public function loadTextDomain(): void
    {
        /** @var string $textDomain */
        $textDomain = $this->config->getKey('Plugin.textdomain');
        $languagesDir = 'languages';
        if ($this->config->hasKey('Plugin/languages_dir')) {
            /**
             * Directory path.
             *
             * @var string $languagesDir
             */
            $languagesDir = $this->config->getKey('Plugin.languages_dir');
        }

        load_plugin_textdomain($textDomain, false, $textDomain . '/' . $languagesDir);
    }

    /**
     * Add webhook message interval to cron schedules.
     *
     * @param array $schedules
     *
     * @return array
     */
    public function webhookMessageInterval(array $schedules): array
    {
        $schedules[self::WEBHOOK_MESSAGE_CRON_INTERVAL] = [
            'interval' => $this->webhookMessageIntervalInSeconds(),
            'display' => esc_html__('Webhook message interval', 'webhook-consumer'),
        ];

        return $schedules;
    }

    /**
     * Get webhook_message interval in seconds.
     *
     * @param int $default
     *
     * @return int
     */
    private function webhookMessageIntervalInSeconds(int $default = 180): int
    {
        $interval = defined('WEBHOOK_MESSAGE_INTERVAL') ?
            intval(WEBHOOK_MESSAGE_INTERVAL) : $default;

        return $interval ?: $default;
    }

    /**
     * Process webhook messages.
     */
    public function processWebhookMessages(): void
    {
        (new Webhook(new Logger()))->handleMessages($this->webhookMessageBufferSize());
    }

    /**
     * Get webhook message buffer size.
     *
     * Number of messages to process per cron job.
     *
     * @param int $default
     *
     * @return int
     */
    private function webhookMessageBufferSize(int $default = 5): int
    {
        $bufferSize = defined('WEBHOOK_MESSAGE_BUFFER_SIZE') ?
            intval(WEBHOOK_MESSAGE_BUFFER_SIZE) : $default;

        return $bufferSize ?: $default;
    }

    /**
     * Callback for 'deleted_post' action.
     *
     * @param int|string $postId
     *
     * @return void
     */
    public function onDeletedPost(mixed $postId): void
    {
        try {
            WebhookMapping::deleteByEntityIdAndType($postId, 'post');
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
    }

    /**
     * Callback for 'deleted_term_taxonomy' action.
     *
     * @param int|string $termId
     *
     * @return void
     */
    public function onDeletedTerm(mixed $termId): void
    {
        try {
            WebhookMapping::deleteByEntityIdAndType($termId, 'term');
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
    }
}
