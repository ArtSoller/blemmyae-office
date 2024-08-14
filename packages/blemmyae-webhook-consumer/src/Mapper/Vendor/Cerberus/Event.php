<?php

/**
 * @author  Nikita Sokolskiy <n_sokolskiy@dotwrk.com>
 * @license proprietary
 */

declare(strict_types=1);

namespace Cra\WebhookConsumer\Mapper\Vendor\Cerberus;

use Cra\BlemmyaeApplications\BlemmyaeApplications;
use Cra\CtLearning\LearningCT;
use Cra\Integrations\WebhookMessenger\ConsumerObjectId;
use Cra\Integrations\WebhookMessenger\EntityId;
use Cra\WebhookConsumer\Mapper\AbstractWordpressWebhookMapper;
use Cra\WebhookConsumer\Mapper\LearningPostTrait;
use Cra\WebhookConsumer\Mapper\MediaTrait;
use DateTime;
use Exception;
use Scm\Tools\Logger;
use Scm\Tools\Utils;
use Scm\Tools\WpCore;

/**
 * Webhook mapper for learning post type.
 */
class Event extends AbstractWordpressWebhookMapper
{
    use LearningPostTrait;
    use MediaTrait;

    public const TYPE = 'learning';

    /**
     * @inheritDoc
     */
    public function upsert(ConsumerObjectId $id, int $timestamp, object $object): EntityId
    {
        $log = fn (string $message) => Logger::log(
            "[Cerberus event, event title {$this->objectTitle()}] " . $message,
            'info'
        );

        $log('Upsert event');
        $webhookMapping = $this->webhookMappingWithFallback($id);
        $this->postId = $this->upsertWebhookMappingAsPost($webhookMapping, $timestamp, [
            'post_title' => $this->objectTitle(),
            'post_content' => $this->objectDescription(),
            'post_status' => WpCore::POST_STATUS_DRAFT,
        ]);

        $log("Updating featured image field, post id: $this->postId");
        $this->updateImageField(
            $this->postId,
            $this->objectImage(),
            LearningCT::GROUP_LEARNING_ADVANCED__FIELD_FEATURED_IMAGE,
            "Featured image for {$this->objectTitle()} cerberus event"
        );

        $log("Updating application field, post id: $this->postId");
        $this->updateApplicationField($this->objectApplication());

        // Dates.
        $this->updateDateField();

        // Location.
        $this->updateLocationField();

        // Organizer details.
        $this->updateAcfField(
            LearningCT::GROUP_LEARNING_ADVANCED__FIELD_ORGANIZER_DETAILS,
            $this->objectOrganizerFields()
        );

        // Vendor.
        $this->updateAcfField(
            LearningCT::GROUP_LEARNING_ADVANCED__FIELD_VENDOR,
            [
                [
                    'acf_fc_layout' => LearningCT::VENDOR__EXTERNAL_EVENT,
                    'url' => $this->objectVenueFields()['venueWebsite']
                ],
            ]
        );

        $log("Sending a notification to email");

        wp_mail(
            match (Utils::environment()) {
                'production', 'prod', 'live' => 'channelevents@cyberriskalliance.com',
                default => 'nikita.sokolsky@cyberriskalliance.com'
            },
            'New event submitted via event submit form - ' . $this->objectTitle(),
            'Link to event in cms - ' .
            // get_edit_post_link does not work, so need to construct the url this way
            implode('/', [get_site_url(), "wp-admin/post.php?post=$this->postId&action=edit"]),
        );

        $log("Returning $this->postId from upsert function");

        return $this->getThisPostEntityId();
    }

    /**
     * Sets|Updates 'Date' ACF field.
     *
     * @return void
     * @throws Exception
     */
    private function updateDateField(): void
    {
        [
            'eventStartDate' => $startDate,
            'eventEndDate' => $endDate
        ] = $this->objectDateFields();

        if (!$startDate || !$endDate) {
            return;
        }

        $this->updateAcfField(
            LearningCT::GROUP_LEARNING_ADVANCED__FIELD_DATE,
            [
                [
                    'start_date' =>
                        (new DateTime($startDate))->format(Utils::ACF_DB_DATETIME_FORMAT),
                    'end_date' =>
                        (new DateTime($endDate))->format(Utils::ACF_DB_DATETIME_FORMAT),
                ],
            ]
        );
    }

    /**
     * Update location field.
     *
     * @return void
     */
    private function updateLocationField(): void
    {
        $objectVenueFields = $this->objectVenueFields();

        $this->updateAcfField(
            LearningCT::GROUP_LEARNING_LOCATION__FIELD_LOCATION,
            [
                'url' => [
                    'title' => 'Virtual location',
                    'url' => $objectVenueFields['venueWebsite'],
                    'target' => '_blank',
                ],
                'phone' => $objectVenueFields['venuePhone'],
                'address' => [
                    'name' => $objectVenueFields['venueName'],
                    'street' => $objectVenueFields['venueAddress'],
                    'locality' => $objectVenueFields['venueCity'],
                    'postal' => null,
                    'region' => $objectVenueFields['venueStateProvince'],
                    'country' => $objectVenueFields['venueCountry'],
                ],
                'map' => null,
                // @todo add google map when its support will be added.
            ]
        );
    }

    /**
     * Get object application.
     *
     * @return string
     */
    private function objectApplication(): string
    {
        // An actual event submit form was located historically on ce2e only - making ce2e default
        return (string)($this->object->application ?? BlemmyaeApplications::CE2E);
    }

    /**
     * Get object title.
     *
     * @return string
     */
    private function objectTitle(): string
    {
        return (string)($this->object->eventTitle ?? '');
    }

    /**
     * Get object description.
     *
     * @return string
     */
    private function objectDescription(): string
    {
        return (string)($this->object->eventDescription ?? '');
    }

    /**
     * Get object description.
     *
     * @return string
     */
    private function objectImage(): string
    {
        return (string)($this->object->image ?? '');
    }

    /**
     * Get object date fields.
     *
     * @return array{'eventStartDate': ?string, 'eventEndDate': ?string}
     */
    private function objectDateFields(): array
    {
        return [
            'eventStartDate' => $this->object->eventStartDate ?? null,
            'eventEndDate' => $this->object->eventEndDate ?? null,
        ];
    }

    /**
     * Get object venue fields.
     *
     * @return array{
     *     'venueName': ?string,
     *     'venueAddress': ?string,
     *     'venueCity': ?string,
     *     'venueStateProvince': ?string,
     *     'venueCountry': ?string,
     *     'venuePhone': ?string,
     *     'venueWebsite': ?string,
     * }
     */
    private function objectVenueFields(): array
    {
        return [
            'venueName' => $this->object->venueName ?? null,
            'venueAddress' => $this->object->venueAddress ?? null,
            'venueCity' => $this->object->venueCity ?? null,
            'venueStateProvince' => $this->object->venueStateProvince ?? null,
            'venueCountry' => $this->object->venueCountry ?? null,
            'venuePhone' => $this->object->venuePhone ?? null,
            'venueWebsite' => $this->object->venueWebsite ?? null,
        ];
    }

    /**
     * Get object organizer fields.
     *
     * @return array{
     *     'name': ?string,
     *     'phone': ?string,
     *     'website': ?string,
     *     'email': ?string,
     * }
     */
    private function objectOrganizerFields(): array
    {
        return [
            'name' => $this->object->organizerName ?? null,
            'phone' => $this->object->organizerPhone ?? null,
            'website' => $this->object->organizerWebsite ?? null,
            'email' => $this->object->organizerEmail ?? null,
        ];
    }
}
