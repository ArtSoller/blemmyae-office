<?php

/**
 * @licence proprietary
 *
 * @author Konstantin Gusev <guvkon.net@icloud.com>
 */

declare(strict_types=1);

namespace Cra\WebhookConsumer;

use Cra\Integrations\WebhookMessenger\ConsumerMapperInterface;
use Cra\Integrations\WebhookMessenger\ConsumerMessageHandler;
use Cra\Integrations\WebhookMessenger\ConsumerMessageInterface;
use Cra\Integrations\WebhookMessenger\ProcessedMessageInterface;
use Cra\WebhookConsumer\Mapper\Vendor\Cerberus\Event as CerberusEvent;
use Cra\WebhookConsumer\Mapper\Vendor\Convertr\Whitepaper as ConvertrWhitepaper;
use Cra\WebhookConsumer\Mapper\Vendor\Ppworks\Announcement as PpworksAnnouncement;
use Cra\WebhookConsumer\Mapper\Vendor\Ppworks\Article as PpworksArticle;
use Cra\WebhookConsumer\Mapper\Vendor\Ppworks\Category as PpworksCategory;
use Cra\WebhookConsumer\Mapper\Vendor\Ppworks\Episode as PpworksEpisode;
use Cra\WebhookConsumer\Mapper\Vendor\Ppworks\Person as PpworksPerson;
use Cra\WebhookConsumer\Mapper\Vendor\Ppworks\Segment as PpworksSegment;
use Cra\WebhookConsumer\Mapper\Vendor\Ppworks\Show as PpworksShow;
use Cra\WebhookConsumer\Mapper\Vendor\Ppworks\Sponsor as PpworksSponsor;
use Cra\WebhookConsumer\Mapper\Vendor\Ppworks\SponsorProgram as PpworksSponsorProgram;
use Cra\WebhookConsumer\Mapper\Vendor\Ppworks\Tag as PpworksTag;
use Cra\WebhookConsumer\Mapper\Vendor\Swoogo\Event as SwoogoEvent;
use Cra\WebhookConsumer\Mapper\Vendor\Swoogo\Session as SwoogoSession;
use Cra\WebhookConsumer\Mapper\Vendor\Swoogo\Speaker as SwoogoSpeaker;
use Cra\WebhookConsumer\Mapper\Vendor\Swoogo\Sponsor as SwoogoSponsor;
use Exception;
use Symfony\Component\Messenger\Envelope;

/**
 * Webhook message handler class.
 */
class WebhookMessageHandler extends ConsumerMessageHandler
{
    public const VENDOR__PPWORKS = 'ppworks';
    public const VENDOR__SWOOGO = 'swoogo';
    public const VENDOR__CONVERTR = 'convertr';
    public const VENDOR__CERBERUS = 'cerberus';

    /**
     * @inheritDoc
     */
    protected function processMessage(ConsumerMessageInterface $message): ProcessedMessageInterface
    {
        $processedMessage = parent::processMessage($message);

        $queue = $message->getReplyTo() === 'none' ?
            SQS_INTEGRATIONS_PROCESSED_QUEUE :
            $message->getReplyTo();
        Webhook::sqsTransport($queue)->send(new Envelope($processedMessage));

        return $processedMessage;
    }

    /**
     * @inheritDoc
     *
     * @throws Exception
     */
    protected function mapperClass(
        string $vendor,
        string $objectType,
        int|string $objectVersion
    ): ConsumerMapperInterface {
        return match ($vendor) {
            self::VENDOR__PPWORKS => $this->ppworksMapperClass($objectType, $objectVersion),
            self::VENDOR__SWOOGO => $this->swoogoMapperClass($objectType, $objectVersion),
            self::VENDOR__CONVERTR => $this->convertrMapperClass($objectType, $objectVersion),
            self::VENDOR__CERBERUS => $this->cerberusMapperClass($objectType, $objectVersion),
            default => throw new Exception("Unknown vendor type: $vendor"),
        };
    }

    /**
     * Get webhook mapper class for ppworks vendor.
     *
     * @param string $objectType
     * @param int|string $objectVersion
     *
     * @return ConsumerMapperInterface
     * @throws Exception
     */
    private function ppworksMapperClass(string $objectType, int|string $objectVersion): ConsumerMapperInterface
    {
        if ($objectVersion !== 1) {
            throw new Exception("Unknown object version: $objectVersion");
        }

        return match ($objectType) {
            PpworksAnnouncement::TYPE => new PpworksAnnouncement(),
            PpworksArticle::TYPE => new PpworksArticle(),
            PpworksCategory::TYPE => new PpworksCategory(),
            PpworksEpisode::TYPE => new PpworksEpisode(),
            PpworksPerson::TYPE__GUEST, PpworksPerson::TYPE__HOST => new PpworksPerson(),
            PpworksSegment::TYPE => new PpworksSegment(),
            PpworksShow::TYPE => new PpworksShow(),
            PpworksSponsor::TYPE => new PpworksSponsor(),
            PpworksSponsorProgram::TYPE => new PpworksSponsorProgram(),
            PpworksTag::TYPE => new PpworksTag(),
            default => throw new Exception("Unknown object type: $objectType"),
        };
    }

    /**
     * Get webhook mapper class for swoogo vendor.
     *
     * @param string $objectType
     * @param int|string $objectVersion
     *
     * @return ConsumerMapperInterface
     * @throws Exception
     */
    private function swoogoMapperClass(string $objectType, int|string $objectVersion): ConsumerMapperInterface
    {
        if ($objectVersion !== 1) {
            throw new Exception("Unknown object version: $objectVersion");
        }
        return match ($objectType) {
            SwoogoEvent::TYPE => new SwoogoEvent(),
            SwoogoSession::TYPE => new SwoogoSession(),
            SwoogoSpeaker::TYPE => new SwoogoSpeaker(),
            SwoogoSponsor::TYPE => new SwoogoSponsor(),
            default => throw new Exception("Unknown object type: $objectType"),
        };
    }

    /**
     * Get webhook mapper class for swoogo vendor.
     *
     * @param string $objectType
     * @param int|string $objectVersion
     *
     * @return ConsumerMapperInterface
     * @throws Exception
     */
    private function convertrMapperClass(string $objectType, int|string $objectVersion): ConsumerMapperInterface
    {
        if ($objectVersion !== 1) {
            throw new Exception("Unknown object version: $objectVersion");
        }
        return match ($objectType) {
            ConvertrWhitepaper::TYPE => new ConvertrWhitepaper(),
            default => throw new Exception("Unknown object type: $objectType"),
        };
    }

    /**
     * Get webhook mapper class for cerberus vendor.
     *
     * @param string $objectType
     * @param int|string $objectVersion
     *
     * @return ConsumerMapperInterface
     * @throws Exception
     */
    private function cerberusMapperClass(string $objectType, int|string $objectVersion): ConsumerMapperInterface
    {
        if ($objectVersion !== 1) {
            throw new Exception("Unknown object version: $objectVersion");
        }

        return match ($objectType) {
            CerberusEvent::TYPE => new CerberusEvent(),
            default => throw new Exception("Unknown object type: $objectType"),
        };
    }
}
