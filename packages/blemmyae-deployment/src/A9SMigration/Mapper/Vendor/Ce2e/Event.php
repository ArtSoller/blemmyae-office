<?php

/**
 * @author  Konstantin Gusev <guvkon.net@icloud.com>
 * @license proprietary
 */

declare(strict_types=1);

namespace Cra\BlemmyaeDeployment\A9SMigration\Mapper\Vendor\Ce2e;

use Cra\BlemmyaeDeployment\A9SMigration\Mapper\Vendor\Alert\Event as AlertEvent;
use Cra\BlemmyaeDeployment\A9SMigration\MigrationHandler;
use Cra\CtLearning\LearningCT;

/**
 * Webhook mapper for saving into company content type.
 */
class Event extends AlertEvent
{
    /**
     * @inheritDoc
     */
    protected function vendor(): string
    {
        return MigrationHandler::VENDOR__CE2E;
    }

    /**
     * @inheritDoc
     */
    protected function brands(): array
    {
        return ['A9s', 'ChannelE2E'];
    }

    /**
     * @inheritDoc
     */
    protected function metaTitle(): string
    {
        return 'ChannelE2E';
    }

    /**
     * @inheritDoc
     */
    protected function vendorType(): string
    {
        return LearningCT::VENDOR_TYPE_GO_TO_WEBINAR;
    }
}
