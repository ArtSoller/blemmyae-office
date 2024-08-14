<?php

/**
 * @licence proprietary
 *
 * @author Konstantin Gusev <guvkon.net@icloud.com>
 * @author  Alexander Kucherov <avdkucherov@gmail.com>
 */

declare(strict_types=1);

namespace Cra\BlemmyaeDeployment\A9SMigration;

use Cra\BlemmyaeApplications\BlemmyaeApplications;
use Cra\BlemmyaeDeployment\A9SMigration;
use Cra\BlemmyaeDeployment\A9SMigration\Mapper\Vendor\Alert\Event as AlertEvent;
use Cra\BlemmyaeDeployment\A9SMigration\Mapper\Vendor\Alert\Organizer as AlertOrganizer;
use Cra\BlemmyaeDeployment\A9SMigration\Mapper\Vendor\Ce2e\Event as Ce2eEvent;
use Cra\BlemmyaeDeployment\A9SMigration\Mapper\Vendor\Ce2e\Organizer as Ce2eOrganizer;
use Cra\BlemmyaeDeployment\A9SMigration\MigrationObjectHandler as AbstractMigrationObjectHandler;
use Cra\Integrations\WebhookMessenger\ConsumerMapperInterface;
use Cra\Integrations\WebhookMessenger\ConsumerMessageInterface;
use Exception;

/**
 * Webhook message handler class.
 */
class MigrationHandler extends AbstractMigrationObjectHandler
{
    public const VENDOR__CE2E = BlemmyaeApplications::CE2E;
    public const VENDOR__ALERT = BlemmyaeApplications::MSSP;


    /**
     * Returns DB name for provided vendor.
     * @param string $vendor
     * @return string
     */
    public static function dbNameByVendor(string $vendor): string
    {
        return match ($vendor) {
            self::VENDOR__ALERT => A9SMIGRATION::ALERT_DB_NAME,
            self::VENDOR__CE2E => A9SMIGRATION::CE2E_DB_NAME,
            default => '',
        };
    }

    /**
     * @inheritDoc
     */
    public function processMessage(ConsumerMessageInterface $message): ConsumerMessageInterface
    {
        return parent::processMessage($message);
    }

    /**
     * @inheritDoc
     *
     * @throws Exception
     */
    protected function mapperClass(string $vendor, string $objectType): ConsumerMapperInterface
    {
        return match ($vendor) {
            self::VENDOR__ALERT => $this->alertMapperClass($objectType),
            self::VENDOR__CE2E => $this->ce2eMapperClass($objectType),
            default => throw new Exception("Unknown vendor type: $vendor"),
        };
    }

    /**
     * Get webhook mapper class for alert vendor.
     *
     * @param string $objectType
     *
     * @return ConsumerMapperInterface
     * @throws Exception
     */
    private function alertMapperClass(string $objectType): ConsumerMapperInterface
    {
        return match ($objectType) {
            AlertOrganizer::TYPE => new AlertOrganizer(),
            AlertEvent::TYPE => new AlertEvent(),
            default => throw new Exception("Unknown object type: $objectType"),
        };
    }

    /**
     * Get webhook mapper class for ce2e vendor.
     *
     * @param string $objectType
     *
     * @return ConsumerMapperInterface
     * @throws Exception
     */
    private function ce2eMapperClass(string $objectType): ConsumerMapperInterface
    {
        return match ($objectType) {
            Ce2eOrganizer::TYPE => new Ce2eOrganizer(),
            Ce2eEvent::TYPE => new Ce2eEvent(),
            default => throw new Exception("Unknown object type: $objectType"),
        };
    }
}
