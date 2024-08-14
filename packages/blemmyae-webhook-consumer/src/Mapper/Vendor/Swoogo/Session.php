<?php

/**
 * @license PROPRIETARY
 *
 * @author  Pavel Lovkii <pavel.lovkiy@gmail.com>
 */

declare(strict_types=1);

namespace Cra\WebhookConsumer\Mapper\Vendor\Swoogo;

use Cra\BlemmyaeApplications\BlemmyaeApplications;
use Cra\CtLearning\SessionCT;
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
use Scm\Tools\Utils;
use Scm\Tools\WpCore;
use WP_Term;

/**
 * Swoogo session mapper class.
 */
class Session extends AbstractWordpressWebhookMapper
{
    use LearningPostTrait;
    use DateTimeTrait;

    public const TYPE = 'session';

    /**
     * @inheritDoc
     */
    public function upsert(ConsumerObjectId $id, int $timestamp, object $object): EntityId
    {
        $this->postId = $this->upsertWebhookMappingAsPost(
            $this->webhookMappingWithFallback($id),
            $timestamp,
            [
                'post_title' => sprintf('%s - %s', $this->objectEventName(), $this->objectName()),
                'post_status' => WpCore::POST_STATUS_DRAFT
            ]
        );

        try {
            $this->updateAcfFields();
            $this->updateLearningLocationField(
                SessionCT::GROUP_LEARNING_LOCATION__FIELD_LOCATION,
                $this->objectLocation(),
                $this->objectVirtualLink()
            );
            $this->updateAcfField(
                SessionCT::GROUP_SESSION_ADVANCED__FIELD_SPEAKERS,
                array_map(static fn(WebhookMapping $mapping) => $mapping->postId, $this->getSpeakerMappings())
            );
        } catch (Exception $exception) {
            $this->cleanupThisPost();

            throw $exception;
        }

        $this->publishThisDraftPost();
        try {
            $this->updateThisPostStatus(WpCore::getPost($this->getRelatedEventPostId())->post_status);
        } catch (Exception $ex) {
            (new Logger())->warning("Unable to update session post status. Exception: {$ex->getMessage()}");
        }

        return $this->getThisPostEntityId();
    }

    /**
     * @inheritDoc
     */
    public function wpEntityBundle(): string
    {
        return SessionCT::POST_TYPE;
    }

    /**
     * Updates 'Session Advanced' ACF field group field.
     *
     * @return void
     *
     * @throws Exception
     */
    private function updateAcfFields(): void
    {
        $relatedEventPostId = $this->getRelatedEventPostId();

        // Get value for application field from the related event.
        $this->updateApplicationField(BlemmyaeApplications::getAppIdByPostId($relatedEventPostId));

        $this->updateAcfField(SessionCT::GROUP_SESSION_ADVANCED__FIELD_EVENT, $relatedEventPostId);

        $this->updateAcfField(
            SessionCT::GROUP_SESSION_ADVANCED__FIELD_DATE_TIME,
            [
                'start_date_time' => $this->objectStartDate() ?
                    Utils::convertDateToAcfDateWithTimezone($this->objectStartDate()) :
                    null,
                'end_date_time' => $this->objectEndDate() ?
                    Utils::convertDateToAcfDateWithTimezone($this->objectEndDate()) :
                    null,
            ]
        );

        $this->updateAcfField(SessionCT::GROUP_SESSION_ADVANCED__FIELD_ABSTRACT, $this->objectDescription());

        $this->updateAcfField(
            SessionCT::GROUP_SESSION_ADVANCED__FIELD_VENDOR,
            [
                [
                    'acf_fc_layout' => SessionCT::VENDOR__SWOOGO,
                    'id' => $this->objectSwoogoId(),
                    'webinar_url' => $this->objectWebinarUrl(),
                    'direct_link' => $this->objectDirectLink(),
                    'virtual_link' => $this->objectVirtualLink(),
                ],
            ]
        );

        $term = get_term_by('name', SessionCT::VENDOR_TYPE__SWOOGO, SessionCT::TAXONOMY__VENDOR_TYPE);
        if ($term instanceof WP_Term) {
            $this->updateAcfField(SessionCT::GROUP_SESSION_ADVANCED__FIELD_VENDOR_TYPE, $term->term_id);
        }
    }

    /**
     * Returns webhook mappings for session speakers.
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
     * Gets ID of parent learning (event) post.
     *
     * @return int Related 'learning' post ID.
     *
     * @throws Exception
     */
    private function getRelatedEventPostId(): int
    {
        $mapping = WebhookMapping::findById(
            new ConsumerObjectId(
                WebhookMessageHandler::VENDOR__SWOOGO,
                Event::TYPE,
                $this->objectEventSwoogoId()
            )
        );
        if (empty($mapping)) {
            throw new Exception("Missing webhook mapping for session's parent event");
        }

        return $mapping->postId;
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
    private function objectEventSwoogoId(): string
    {
        return (string)($this->object->eventId ?? '');
    }

    /**
     * @return string
     */
    private function objectName(): string
    {
        return (string)($this->object->name ?? '');
    }

    private function objectEventName(): string
    {
        return (string)($this->object->eventName ?? '');
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
    private function objectWebinarUrl(): string
    {
        return (string)($this->object->webinarUrl ?? '');
    }

    /**
     * @return string
     */
    private function objectDirectLink(): string
    {
        return (string)($this->object->directLink ?? '');
    }

    /**
     * @return string
     */
    private function objectVirtualLink(): string
    {
        return (string)($this->object->virtualLink ?? '');
    }

    /**
     * Get object's location field.
     *
     * This field isn't populated currently. Probably it will be dropped in the future.
     *
     * @return null|object{
     *        'name'?: string,
     *        'company'?: string,
     *        'line_1'?: ?string,
     *        'line_2'?: ?string,
     *        'line_3'?: ?string,
     *        'city'?: ?string,
     *        'state'?: ?string,
     *        'zip'?: ?string,
     *        'country'?: array{
     *            'code'?: ?string,
     *            'name'?: ?string,
     *            'continent'?: ?string,
     *            'zipcode_required'?: ?bool,
     *            'currency_code'?: ?string,
     *            'tax_name'?: ?string,
     *        },
     *        'country_code'?: ?string,
     *        'phone'?: ?string,
     *        'website'?: ?string,
     *        'latitude'?: ?string,
     *        'longitude'?: ?string,
     *    }
     */
    private function objectLocation(): ?object
    {
        // @phpstan-ignore-next-line It complains that this is a stdClass.
        return isset($this->object->location) ? (object)$this->object->location : null;
    }

    /**
     * @return int[]
     */
    private function objectSpeakerSwoogoIds(): array
    {
        $ids = $this->object->speakerIds ?? [];
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
}
