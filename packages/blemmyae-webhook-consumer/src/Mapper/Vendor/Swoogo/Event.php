<?php

/**
 * @license PROPRIETARY
 *
 * @author  Pavel Lovkii <pavel.lovkiy@gmail.com>
 */

declare(strict_types=1);

namespace Cra\WebhookConsumer\Mapper\Vendor\Swoogo;

use Cra\BlemmyaeApplications\BlemmyaeApplications;
use Cra\BlemmyaeApplications\CerberusApps;
use Cra\BlemmyaeApplications\Entity\Term;
use Cra\CtLearning\LearningCT;
use Cra\Integrations\WebhookMessenger\ConsumerObjectId;
use Cra\Integrations\WebhookMessenger\EntityId;
use Cra\WebhookConsumer\Logger;
use Cra\WebhookConsumer\Mapper\AbstractWordpressWebhookMapper;
use Cra\WebhookConsumer\Mapper\DateTimeTrait;
use Cra\WebhookConsumer\Mapper\LearningPostTrait;
use Cra\WebhookConsumer\WebhookMapping;
use Cra\WebhookConsumer\WebhookMessageHandler;
use DateTime;
use Exception;
use Scm\Archived_Post_Status\Options;
use Scm\Tools\Utils;
use Scm\Tools\WpCore;

/**
 * Swoogo event mapper class.
 */
class Event extends AbstractWordpressWebhookMapper
{
    use LearningPostTrait;
    use DateTimeTrait;

    public const TYPE = 'event';
    public const STATUS_LIVE = 'live';

    /**
     * Maps Swoogo event types to learning type taxonomy.
     */
    private const EVENT_TYPE_MAPPING = [
        'Webinar' => 'Cybercast',
        'eRoundtable' => 'eRoundtable',
        'Live Conference' => 'Live Conference',
        'Leadership Dinner' => 'Leadership Dinner',
        'Virtual Event' => 'Virtual Event',
    ];

    /**
     * @inheritDoc
     */
    public function upsert(ConsumerObjectId $id, int $timestamp, object $object): EntityId
    {
        $this->postId = $this->upsertWebhookMappingAsPost(
            $this->webhookMappingWithFallback($id),
            $timestamp,
            [
                'post_title' => $this->objectName(),
                'post_content' => $this->objectDescription(),
                'post_status' => WpCore::POST_STATUS_DRAFT,
            ]
        );

        try {
            $this->updateAcfFields();
        } catch (Exception $exception) {
            $this->cleanupThisPost();

            throw $exception;
        }

        $this->publishThisDraftPost();
        if ($this->objectStatus() !== self::STATUS_LIVE) {
            $this->updateThisPostStatus(Options::ARCHIVE_STATUS);
        }

        return $this->getThisPostEntityId();
    }

    /**
     * Update ACF fields of the event.
     *
     * It also updates terms for the taxonomy fields.
     *
     * @return void
     * @throws Exception
     */
    private function updateAcfFields(): void
    {
        // Application.
        WpCore::setPostTerms(
            BlemmyaeApplications::TAXONOMY,
            Term::getAppTermIdByAppSlug(BlemmyaeApplications::CRC),
            $this->postId,
            CerberusApps::APPLICATION_FIELD,
        );

        // Brand.
        $this->setTermsOnTaxonomyField(
            LearningCT::TAXONOMY__BRAND,
            [LearningCT::BRAND__TERM__CSF],
            LearningCT::GROUP_LEARNING_TAXONOMY__FIELD_BRAND,
        );

        // Vendor taxonomy.
        WpCore::setPostTerms(
            LearningCT::TAXONOMY__VENDOR_TYPE,
            WpCore::getTermByName(LearningCT::TAXONOMY__VENDOR_TYPE, LearningCT::VENDOR_TYPE__SWOOGO)->term_id,
            $this->postId,
            LearningCT::GROUP_LEARNING_ADVANCED__FIELD_VENDOR_TYPE,
        );

        // Vendor.
        $this->updateAcfField(
            LearningCT::GROUP_LEARNING_ADVANCED__FIELD_VENDOR,
            [
                [
                    'acf_fc_layout' => LearningCT::VENDOR__SWOOGO,
                    'url' => $this->objectUrl(),
                    'id' => $this->objectSwoogoId(),
                ],
            ]
        );

        // Date.
        $this->updateAcfField(
            LearningCT::GROUP_LEARNING_ADVANCED__FIELD_DATE,
            [
                [
                    'start_date' => $this->objectStartDate() ?
                        Utils::convertDateToAcfDateWithTimezone($this->objectStartDate()) :
                        null,
                    'end_date' => $this->objectEndDate() ?
                        Utils::convertDateToAcfDateWithTimezone($this->objectEndDate()) :
                        null,
                ],
            ]
        );

        // Location.
        $this->updateLearningLocationField(
            LearningCT::GROUP_LEARNING_LOCATION__FIELD_LOCATION,
            $this->objectLocation(),
            $this->objectVirtualLocation(),
        );

        // Learning type.
        $this->setTermsOnTaxonomyField(
            LearningCT::TAXONOMY__LEARNING_TYPE,
            $this->objectEventType(),
            LearningCT::GROUP_LEARNING_TAXONOMY__FIELD_LEARNING_TYPE,
        );

        // Topic.
        $this->setTermsOnTaxonomyField(
            LearningCT::TAXONOMY__TOPIC,
            $this->objectTopic(),
            LearningCT::GROUP_LEARNING_TAXONOMY__FIELD_TOPIC,
            LearningCT::TERM_UNCATEGORIZED
        );

        // CISO community region.
        $this->setTermsOnTaxonomyField(
            LearningCT::TAXONOMY__COMMUNITY_REGION,
            $this->objectRegion(),
            LearningCT::GROUP_LEARNING_ADVANCED__FIELD_CISO_COMMUNITY_REGION,
        );

        // Company (aka sponsors).
        $this->updateAcfField(
            LearningCT::GROUP_LEARNING_ADVANCED__FIELD_COMPANY,
            array_map(static fn(WebhookMapping $sponsor) => $sponsor->postId, $this->getSponsorMappings())
        );

        // Speakers.
        $this->updateAcfField(
            LearningCT::GROUP_LEARNING_ADVANCED__FIELD_SPEAKERS,
            array_map(
                static fn(WebhookMapping $mapping) => ['speaker' => [$mapping->postId]],
                $this->getSpeakerMappings()
            ),
        );
    }

    /**
     * Updates taxonomy field with a given values (terms).
     *
     * If no terms found and no default term provided, field will not be updated.
     * Sets the post terms to a given|default terms.
     *
     * @param string $taxonomy Taxonomy name.
     * @param string[] $terms Array of terms to set to post.
     * @param string $field ACF Field key.
     * @param string|null $defaultTerm If no term found, this value will be used.
     *
     * @return void
     * @throws Exception
     */
    protected function setTermsOnTaxonomyField(
        string $taxonomy,
        array $terms,
        string $field,
        ?string $defaultTerm = null
    ): void {
        $outputTerms = [];
        foreach ($terms as $termName) {
            try {
                $outputTerms[] = WpCore::getTermByName($taxonomy, $termName)->term_id;
            } catch (Exception $exception) {
                (new Logger())->warning("Term '$termName', taxonomy '$taxonomy' - {$exception->getMessage()}");
            }
        }
        if (!$outputTerms && $defaultTerm) {
            $outputTerms[] = WpCore::getTermByName($taxonomy, $defaultTerm)->term_id;
        }
        WpCore::setPostTerms($taxonomy, $outputTerms, $this->postId, $field);
    }

    /**
     * Returns webhook mappings for event sponsors.
     *
     * @return WebhookMapping[]
     *
     * @throws Exception
     */
    private function getSponsorMappings(): array
    {
        // For some Swoogo syncs Integrations doesn't send sponsors at all.
        return WebhookMapping::findMultiple(
            $this->objectSponsorIds(),
            Sponsor::TYPE,
            WebhookMessageHandler::VENDOR__SWOOGO,
            false
        );
    }

    /**
     * Returns webhook mappings for event speakers.
     *
     * @return WebhookMapping[]
     *
     * @throws Exception
     */
    private function getSpeakerMappings(): array
    {
        // For some Swoogo syncs Integrations doesn't send speakers at all.
        return WebhookMapping::findMultiple(
            $this->objectSpeakerSwoogoIds(),
            Speaker::TYPE,
            WebhookMessageHandler::VENDOR__SWOOGO,
            false
        );
    }

    /**
     * @return string
     */
    private function objectSwoogoId(): string
    {
        return (string)($this->object->id ?? '');
    }

    /**
     * @return string
     */
    private function objectName(): string
    {
        return (string)($this->object->name ?? '');
    }

    /**
     * @return string
     */
    private function objectDescription(): string
    {
        return (string)($this->object->description ?? '');
    }

    /**
     * @return string
     */
    private function objectUrl(): string
    {
        return (string)($this->object->url ?? '');
    }

    /**
     * @return string[]
     */
    private function objectSpeakerSwoogoIds(): array
    {
        $ids = $this->object->speakerIds ?? [];
        return is_array($ids) ? $ids : [];
    }

    /**
     * @return string[]
     */
    private function objectSponsorIds(): array
    {
        $ids = $this->object->sponsorIds ?? [];
        return is_array($ids) ? $ids : [];
    }

    /**
     * @return DateTime|null
     *
     * @throws Exception
     */
    private function objectStartDate(): ?DateTime
    {
        return $this->convertToDateTime($this->object->startDateTime ?? null);
    }

    /**
     * @return DateTime|null
     *
     * @throws Exception
     */
    private function objectEndDate(): ?DateTime
    {
        return $this->convertToDateTime($this->object->endDateTime ?? null);
    }

    /**
     * @return null|object{
     *       'name'?: string,
     *       'company'?: string,
     *       'line_1'?: ?string,
     *       'line_2'?: ?string,
     *       'line_3'?: ?string,
     *       'city'?: ?string,
     *       'state'?: ?string,
     *       'zip'?: ?string,
     *       'country'?: array{
     *           'code'?: ?string,
     *           'name'?: ?string,
     *           'continent'?: ?string,
     *           'zipcode_required'?: ?bool,
     *           'currency_code'?: ?string,
     *           'tax_name'?: ?string,
     *       },
     *       'country_code'?: ?string,
     *       'phone'?: ?string,
     *       'website'?: ?string,
     *       'latitude'?: ?string,
     *       'longitude'?: ?string,
     *   }
     */
    private function objectLocation(): ?object
    {
        // @phpstan-ignore-next-line It complains that this is a stdClass.
        return isset($this->object->location) ? (object)$this->object->location : null;
    }

    /**
     * @return string|null
     */
    private function objectVirtualLocation(): ?string
    {
        return $this->object->swoogoVirtualLocation ?? null;
    }

    /**
     * @return string[]
     */
    private function objectRegion(): array
    {
        return !empty($this->object->region) ? [$this->object->region] : [];
    }

    /**
     * @return string[]
     */
    private function objectEventType(): array
    {
        $eventType = self::EVENT_TYPE_MAPPING[$this->object->eventType ?? ''] ?? '';
        return $eventType ? [$eventType] : [];
    }

    /**
     * @return string[]
     */
    private function objectTopic(): array
    {
        return !empty($this->object->topic) ? [$this->object->topic] : [];
    }

    /**
     * @return string
     */
    private function objectStatus(): string
    {
        return $this->object->status ?? '';
    }
}
