<?php

/**
 * @author  Konstantin Gusev <guvkon.net@icloud.com>
 * @license proprietary
 * @noinspection PhpUnused
 */

declare(strict_types=1);

namespace Cra\WebhookConsumer\Command;

use Cra\Integrations\WebhookMessenger\ConsumerObjectId;
use Cra\Integrations\WebhookMessenger\WebhookMessage;
use Cra\WebhookConsumer\Mapper\Vendor\Ppworks\Episode;
use Cra\WebhookConsumer\Messenger;
use Cra\WebhookConsumer\WebhookMapping;
use Cra\WebhookConsumer\WebhookMessageHandler;
use Exception;
use Generator;
use stdClass;
use WP_CLI;

/**
 * Class which implements `webhook` command for WP CLI.
 */
class Webhook
{
    /**
     * Re-consume webhook data from DB.
     *
     * Keep in mind that timestamps of re-consumed data are going to be incremented by 1 ms.
     *
     * @param array<int, string> $args
     * @param array<string, string> $assocArgs
     *
     * ## OPTIONS
     *
     * <vendor>
     * : Vendor name to re-consume.
     *
     * <type>
     * : Object type to re-consume.
     *
     * [--id=<id>]
     * : ID of the object to re-consume.
     *
     * ## EXAMPLES
     *
     *     wp webhook reconsume ppworks segment
     */
    public function reconsume(array $args, array $assocArgs): void
    {
        $vendor = $args[0] ?? null;
        $type = $args[1] ?? null;
        $id = $assocArgs['id'] ?? null;
        if (!$vendor || !$type) {
            WP_CLI::error('<vendor> and <type> arguments are required!');

            return;
        }

        WP_CLI::warning('Timestamps of re-consumed data are going to be incremented by 1 ms!');

        /** @var WebhookMapping $webhookMapping */
        foreach ($this->yieldWebhookMappingsToReconsume($vendor, $type, $id) as $webhookMapping) {
            $objectDetails = implode(
                ', ',
                [
                    "id = $webhookMapping->id",
                    "post_id = $webhookMapping->postId",
                    "entity_type = $webhookMapping->entityType",
                ]
            );
            try {
                Messenger::instance()->bus()->dispatch(
                    new WebhookMessage([
                        'event' => 'update',
                        'vendor' => $vendor,
                        'objectType' => $type,
                        'uuid' => $webhookMapping->id,
                        'object' => $webhookMapping->object,
                        'objectVersion' => $this->objectVersion($webhookMapping),
                        'timestamp' => $webhookMapping->timestamp + 1,
                        'issuer' => 'blemmyae',
                    ])
                );
                WP_CLI::success("Object has been processed - $objectDetails.");
            } catch (Exception $exception) {
                WP_CLI::warning("An error has occurred while processing object - $objectDetails!");
                WP_CLI::warning($exception->getMessage());
            }
        }
    }

    /**
     * Yield webhook mappings to re-consume.
     *
     * @param string $vendor
     * @param string $type
     * @param string|null $id
     *
     * @return Generator<WebhookMapping>
     */
    private function yieldWebhookMappingsToReconsume(string $vendor, string $type, ?string $id = null): Generator
    {
        if ($id) {
            $entities = WebhookMapping::findById(new ConsumerObjectId($vendor, $type, $id), true);
            if ($entities) {
                yield $entities;
            }

            return;
        }

        foreach (WebhookMapping::findByVendorAndType($vendor, [$type], true) as $webhookMapping) {
            yield $webhookMapping;
        }
    }

    /**
     * Figure out object version.
     *
     * @param WebhookMapping $webhookMapping
     *
     * @return int
     * @noinspection PhpUnusedParameterInspection
     */
    private function objectVersion(WebhookMapping $webhookMapping): int
    {
        // Update logic if new versions of objects are introduced.
        return 1;
    }

    /**
     * Create test data.
     * args[0] - vendor type
     * args[1] - object type
     * Example usages:
     * `wp webhook createTestData` - will run tests for each vendor and each object type
     * `wp webhook createTestData <vendor>` - will run tests for specified vendor only
     * `wp webhook createTestData <vendor> <objectType>` - will run tests for specified vendor and object type only
     *
     * @param array<int, string> $args
     * @param array<string, string> $assocArgs
     *
     * @return void
     * @noinspection PhpUnusedParameterInspection
     */
    public function createTestData(array $args, array $assocArgs): void
    {
        foreach (
            $this->yieldTestData(
                false,
                $args[0] ?? null,
                $args[1] ?? null
            ) as $object
        ) {
            $vendor = $object->vendor;
            try {
                Messenger::instance()->bus()->dispatch(
                    new WebhookMessage([
                        'event' => 'create',
                        'vendor' => $vendor,
                        'objectType' => $object->objectType,
                        'uuid' => $object->id,
                        'object' => $object,
                        'objectVersion' => 1,
                        'timestamp' => $this->timestamp(),
                        'issuer' => 'blemmyae',
                    ])
                );
                $this->logSuccessEvent($object, 'create');

                // PORT-2238 test cases.
                // Changing guest name.
                if (in_array($object->id, ["guest-1", "host-1"], true)) {
                    $_object = clone $object;
                    $_object->firstname .= " Changed";
                    $_object->lastname .= " Changed";
                    Messenger::instance()->bus()->dispatch(
                        new WebhookMessage([
                            'event' => 'update',
                            'vendor' => $vendor,
                            'objectType' => $_object->objectType,
                            'uuid' => $_object->id,
                            'object' => $_object,
                            'objectVersion' => 1,
                            'timestamp' => $this->timestamp(),
                            'issuer' => 'blemmyae',
                        ])
                    );
                    $this->logSuccessEvent($_object, 'update');
                }

                // PORT-2119 test cases.
                // Directly delete segment.
                if ($object->id === 'segment-3') {
                    Messenger::instance()->bus()->dispatch(
                        new WebhookMessage([
                            'event' => 'delete',
                            'vendor' => $vendor,
                            'objectType' => $object->objectType,
                            'uuid' => $object->id,
                            'object' => (object)[],
                            'objectVersion' => 1,
                            'timestamp' => $this->timestamp(),
                            'issuer' => 'blemmyae',
                        ])
                    );
                    $this->logSuccessEvent($object, 'delete');
                }
                // Indirectly delete segment by updating parent episode.
                if ($object->id === 'segment-99') {
                    $episodeMapping = WebhookMapping::findById(
                        new ConsumerObjectId($vendor, Episode::TYPE, 'episode-vault-1'),
                        true
                    );
                    if (!$episodeMapping) {
                        throw new Exception('Cannot just created episode episode-vault-1');
                    }

                    /** @var stdClass $episode */
                    $episode = $episodeMapping->object;
                    $episode->segments = ['segment-9'];
                    Messenger::instance()->bus()->dispatch(
                        new WebhookMessage([
                            'event' => 'update',
                            'vendor' => $vendor,
                            'objectType' => Episode::TYPE,
                            'uuid' => $episodeMapping->id,
                            'object' => $episode,
                            'objectVersion' => 1,
                            'timestamp' => $this->timestamp(),
                            'issuer' => 'blemmyae',
                        ])
                    );
                    $this->logSuccessEvent($episode, 'update');
                }
            } catch (Exception $exception) {
                WP_CLI::warning("Error processing $object->id has occurred.");
                WP_CLI::warning($exception->getMessage());
            }
        }
    }

    /**
     * Delete test data.
     *
     * @param int[] $args
     * @param array<string, string> $assocArgs
     *
     * @return void
     * @noinspection PhpUnusedParameterInspection
     */
    public function deleteTestData(array $args, array $assocArgs): void
    {
        foreach ($this->yieldTestData(true) as $object) {
            try {
                Messenger::instance()->bus()->dispatch(
                    new WebhookMessage([
                        'event' => 'delete',
                        'vendor' => $object->vendor,
                        'objectType' => $object->objectType,
                        'uuid' => $object->id,
                        'object' => (object)[],
                        'objectVersion' => 1,
                        'timestamp' => $this->timestamp(),
                        'issuer' => 'blemmyae',
                    ])
                );
                $this->logSuccessEvent($object, 'delete');
            } catch (Exception $exception) {
                WP_CLI::warning("Error deleting $object->id has occurred.");
                WP_CLI::warning($exception->getMessage());
            }
        }
    }

    /**
     * Yield test data.
     *
     * @param bool $reverse
     * @param string|null $requestedVendor
     * @param string|null $requestedObjectType
     *
     * @return Generator
     */
    private function yieldTestData(
        bool $reverse = false,
        ?string $requestedVendor = null,
        ?string $requestedObjectType = null
    ): Generator {
        // @phpstan-ignore-next-line
        $testDataDir = WEBHOOK_CONSUMER_DIR . 'src/Command/test-data';
        $objectTypesByVendor = [
            WebhookMessageHandler::VENDOR__PPWORKS => [
                'show',
                'announcement',
                'article',
                'guest',
                'host',
                'sponsor',
                'sponsor_program',
                'episode',
                'segment',
            ],
            WebhookMessageHandler::VENDOR__CERBERUS => [
                'learning',
            ],
            WebhookMessageHandler::VENDOR__SWOOGO => [
                'speaker',
                'sponsor',
                'event',
                'session',
            ],
        ];
        foreach ($objectTypesByVendor as $vendor => $orderedObjectTypes) {
            if ($requestedVendor && $vendor != $requestedVendor) {
                continue;
            }
            if ($reverse) {
                $objectTypesByVendor = array_reverse($objectTypesByVendor);
            }
            foreach ($orderedObjectTypes as $objectType) {
                if ($requestedObjectType && $objectType != $requestedObjectType) {
                    continue;
                }

                // Make sure that files are sorted by filename (without extension).
                $paths = array_map(
                    static fn(string $path) => str_replace('.json', '', $path),
                    glob("$testDataDir/$vendor/$objectType/*.json") ?: []
                );
                sort($paths);
                foreach ($paths as $objectPath) {
                    $objectPath .= '.json';
                    $object = json_decode(file_get_contents($objectPath) ?: '{}');
                    $object->objectType = $objectType;
                    $object->vendor = $vendor;
                    $object->_filename = basename($objectPath);
                    yield $object;
                }
            }
        }
    }

    /**
     * Get current timestamp in milliseconds.
     *
     * @return int
     */
    private function timestamp(): int
    {
        return (int)round(microtime(true) * 1000);
    }

    /**
     * Log success event
     *
     * @param object $object Webhook object.
     * @param string $event one of the following: create, update, delete.
     *
     * @return void
     */
    private function logSuccessEvent(object $object, string $event): void
    {
        $id = $object->id ?? null;
        $filename = $object->_filename ?? '';
        WP_CLI::success("Object $id has been {$event}d. File = $filename");
    }
}
