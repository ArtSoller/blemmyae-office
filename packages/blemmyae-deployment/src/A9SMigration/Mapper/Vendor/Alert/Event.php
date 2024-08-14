<?php

/**
 * @author  Konstantin Gusev <guvkon.net@icloud.com>
 * @license proprietary
 */

declare(strict_types=1);

namespace Cra\BlemmyaeDeployment\A9SMigration\Mapper\Vendor\Alert;

use Cra\BlemmyaeApplications\BlemmyaeApplications;
use Cra\BlemmyaeApplications\CerberusApps;
use Cra\BlemmyaeApplications\Entity\Term;
use Cra\BlemmyaeDeployment\A9SMigration;
use Cra\BlemmyaeDeployment\A9SMigration\AbstractPostMigrationMapper;
use Cra\BlemmyaeDeployment\A9SMigration\MigrationHandler;
use Cra\BlemmyaeDeployment\A9SMigration\MigrationMapping;
use Cra\CtCompanyProfile\CompanyProfileCT;
use Cra\CtLearning\LearningCT;
use Cra\Integrations\WebhookMessenger\ConsumerObjectId;
use Cra\Integrations\WebhookMessenger\EntityId;
use Cra\WebhookConsumer\Logger;
use DateTime;
use Exception;
use Scm\Tools\Utils;
use WP_Query;
use WP_Term;

/**
 * Webhook mapper for saving into company content type.
 */
class Event extends AbstractPostMigrationMapper
{
    public const TYPE = 'event';
    public const STATUS_PUBLISH = 'publish';
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING = 'pending';
    public const STATUS_ARCHIVE = 'archive';

    /**
     * @return string
     */
    protected function vendor(): string
    {
        return MigrationHandler::VENDOR__ALERT;
    }

    /**
     * @return string[]
     */
    protected function brands(): array
    {
        return ['A9s', 'MSSP Alert'];
    }

    /**
     * @return string
     */
    protected function metaTitle(): string
    {
        return 'MSSP Alert';
    }

    /**
     * @return string
     */
    protected function vendorType(): string
    {
        return LearningCT::VENDOR_TYPE_GO_TO_WEBINAR;
    }

    /**
     * @inheritDoc
     */
    public function upsert(ConsumerObjectId $id, int $timestamp, object $object): EntityId
    {
        $postId = $this->upsertLearningPost(
            $id,
            $timestamp,
            $this->objectName(),
            $this->objectDescription()
        );
        try {
            $this
                ->updateLearningAcfFields($postId)
                ->updateLearningPostLocationField(
                    LearningCT::GROUP_LEARNING_LOCATION__FIELD_LOCATION,
                    $postId,
                    $this->objectLocation(),
                    $this->objectVirtualLocation(),
                )
                ->updateLearningTaxonomyGroup($postId);
        } catch (Exception $exception) {
            $post = get_post($postId);
            // Clean up if the post has been just created.
            if ($post->post_status === self::STATUS_DRAFT && !wp_delete_post($postId, true)) {
                (new Logger())->warning("Unable to delete post during clean up: ID = $postId");
            }

            throw $exception;
        }

        $postStatus = get_post($postId) ? get_post($postId)->post_status : null;

        if (!empty($postStatus) && $postStatus !== self::STATUS_ARCHIVE) {
            match ($this->objectStatus()) {
                self::STATUS_PUBLISH => wp_publish_post($postId),
                self::STATUS_DRAFT, self::STATUS_PENDING =>
                wp_update_post([
                    'ID' => $postId,
                    'post_status' => 'draft',
                ]),
                default => wp_update_post([
                    'ID' => $postId,
                    'post_status' => 'archive',
                ]),
            };
        }

        return new EntityId($postId, 'post');
    }

    /**
     * Updates 'Learning Advanced' ACF field group fields.
     *
     * @param int $postId
     *
     * @return $this
     *
     * @throws Exception On Sponsors update error.
     */
    protected function updateLearningAcfFields(int $postId): self
    {
        return $this
            ->updateApplicationField($postId)
            ->updateBrandField($postId)
            ->updateLearningVendorTypeField($postId)
            ->updateVendorField($postId)
            ->updateDateField($postId)
            ->updateSponsorsField($postId)
            ->updateFeaturedImage($postId)
            ->updateMetaFields($postId);
    }

    /**
     * Sets|Updates 'Vendor' ACF field.
     *
     * @param int $postId
     *
     * @return $this
     */
    private function updateVendorField(int $postId): self
    {
        update_field(
            LearningCT::GROUP_LEARNING_ADVANCED__FIELD_VENDOR,
            [
                [
                    'acf_fc_layout' => LearningCT::VENDOR__GOTOWEBINAR,
                    'event_url' => $this->objectUrl(),
                ],
            ],
            $postId
        );

        return $this;
    }

    /**
     * Updates 'Learning Taxonomy' ACF field group fields.
     *
     * @param int $postId
     *
     * @return $this
     */
    protected function updateLearningTaxonomyGroup(int $postId): self
    {
        $this->updateTermsOnTaxonomyField(
            $postId,
            LearningCT::GROUP_LEARNING_TAXONOMY__FIELD_LEARNING_TYPE,
            LearningCT::TAXONOMY__LEARNING_TYPE,
            [$this->objectEventType()]
        )->updateTermsOnTaxonomyField(
            $postId,
            LearningCT::GROUP_LEARNING_TAXONOMY__FIELD_BRAND,
            LearningCT::TAXONOMY__BRAND,
            $this->brands()
        )->updateTermsOnTaxonomyField(
            $postId,
            LearningCT::GROUP_LEARNING_TAXONOMY__FIELD_TOPIC,
            LearningCT::TAXONOMY__TOPIC,
            [$this->objectTopic()],
            LearningCT::TERM_UNCATEGORIZED
        );

        return $this;
    }

    /**
     * Updates Company (Sponsors) field for event.
     *
     * @param int $postId
     *
     * @return $this
     *
     * @throws Exception
     */
    protected function updateSponsorsField(int $postId): self
    {
        $currentSponsors = get_field(
            LearningCT::GROUP_LEARNING_ADVANCED__FIELD_COMPANY,
            $postId,
            false
        ) ?: [];

        $sponsorMappings = $this->getSponsorMappings();

        $sponsors = [];
        foreach ($sponsorMappings as $sponsor) {
            $sponsors[$sponsor->postId] = $sponsor->postId;
        }

        foreach ($currentSponsors as $sponsorField) {
            $sponsors[$sponsorField] = $sponsorField;
        }

        update_field(
            LearningCT::GROUP_LEARNING_ADVANCED__FIELD_COMPANY,
            array_values($sponsors),
            $postId
        );

        return $this;
    }

    /**
     * Sets|Updates 'Date' ACF field.
     *
     * @param int $postId
     *
     * @return $this
     * @throws Exception
     */
    protected function updateDateField(int $postId): self
    {
        update_field(
            LearningCT::GROUP_LEARNING_ADVANCED__FIELD_DATE,
            [
                [
                    'start_date' => $this->objectEventStartDateUTC() ?
                        Utils::convertDateToAcfDateWithTimezone(new DateTime($this->objectEventStartDateUTC())) :
                        null,
                    'end_date' => $this->objectEventEndDateUTC() ?
                        Utils::convertDateToAcfDateWithTimezone(new DateTime($this->objectEventEndDateUTC())) :
                        null,
                ],
            ],
            $postId
        );

        return $this;
    }

    /**
     * Sets|Updates 'Learning Vendor Type' ACF field and attach same-named taxonomy term to post.
     *
     * @param int $postId
     *
     * @return $this
     */
    protected function updateLearningVendorTypeField(int $postId): self
    {
        $term = get_term_by('name', $this->vendorType(), LearningCT::TAXONOMY__VENDOR_TYPE);

        if ($term instanceof WP_Term) {
            update_field(
                LearningCT::GROUP_LEARNING_ADVANCED__FIELD_VENDOR_TYPE,
                $term,
                $postId
            );
            wp_set_post_terms($postId, $term->name, LearningCT::TAXONOMY__VENDOR_TYPE);
        }

        return $this;
    }

    /**
     * Sets|Updates 'Learning Featured Image' ACF field and attach image to post.
     *
     * @param int $postId
     *
     * @return $this
     */
    protected function updateFeaturedImage(int $postId): self
    {
        if ($this->objectThumbnailId() === '') {
            (new Logger())->warning("There is no Featured Image.");
            return $this;
        }

        $featuredImage = A9SMigration::getMapping(
            'media',
            $this->vendor(),
            $this->objectThumbnailId()
        );
        $id = $featuredImage ? (int)$featuredImage[0] : 0;

        if (!get_field(LearningCT::GROUP_LEARNING_ADVANCED__FIELD_FEATURED_IMAGE, $postId, false)) {
            // Non-vendor specific featured_image is missing for Learning. Setting.
            update_field(LearningCT::GROUP_LEARNING_ADVANCED__FIELD_FEATURED_IMAGE, $id, $postId);
        }

        return $this;
    }

    /**
     * Updates 'Learning Meta Title and Description' ACF field.
     *
     * @param int $postId
     *
     * @return $this
     */
    protected function updateMetaFields(int $postId): self
    {
        $metaTitle = $this->objectName() . ' - ' . $this->metaTitle();
        $metaDesc = $this->objectMetaDescription();

        if ($metaTitle && !get_field(LearningCT::GROUP_META__FIELD_TITLE, $postId)) {
            update_field(LearningCT::GROUP_META__FIELD_TITLE, $metaTitle, $postId);
        }

        if ($metaDesc && !get_field(LearningCT::GROUP_META__FIELD_DESCRIPTION, $postId)) {
            update_field(LearningCT::GROUP_META__FIELD_DESCRIPTION, $metaDesc, $postId);
        }

        return $this;
    }

    /**
     * Updates application field value.
     *
     * @param int $postId
     * @return $this
     */
    protected function updateApplicationField(int $postId): self
    {
        $term = Term::getAppTermBy('slug', $this->vendor());
        if ($term instanceof WP_Term) {
            update_field(
                CerberusApps::APPLICATION_FIELD,
                $term->term_id,
                $postId
            );
            update_post_meta(
                $postId,
                CerberusApps::APPLICATION_FIELD_META_KEY,
                $term->term_id
            );
            wp_set_post_terms($postId, $term->name, BlemmyaeApplications::TAXONOMY);
        }

        return $this;
    }

    /**
     * Updates brand field value.
     *
     * @param int $postId
     * @return $this
     */
    protected function updateBrandField(int $postId): self
    {
        $brands = $this->brands();
        if (empty($brands)) {
            return $this;
        }

        $brandTerms = [];
        foreach ($brands as $brand) {
            $brandTerms [] = get_term_by('name', $brand, 'brand')->term_id;
        }

        if (count($brandTerms) < 2) {
            return $this;
        }

        update_field(
            LearningCT::GROUP_LEARNING_TAXONOMY__FIELD_BRAND,
            $brandTerms,
            $postId
        );

        return $this;
    }

    /**
     * Returns webhook mappings for event sponsors.
     *
     * @return array
     *
     * @throws Exception
     */
    protected function getSponsorMappings(): array
    {
        $mapping = [];
        try {
            $mapping = MigrationMapping::findMultiple(
                $this->objectSponsorIds(),
                Organizer::TYPE,
                $this->vendor()
            );
        } catch (Exception $exception) {
            (new Logger())->warning("Unable to find mapping pair for Sponsors.");
        }

        return empty($this->objectSponsorIds()) ? [] : $mapping;
    }

    /**
     * @inheritDoc
     */
    public function wpEntityBundle(): string
    {
        return LearningCT::POST_TYPE;
    }

    /**
     * Finds current company's post ID.
     * Found if 'Company Profile' post with the same name is published.
     *
     * @return int 'Company Profile' post type post ID or 0 if not found.
     */
    protected function findCompanyPostId(): int
    {
        $query = new WP_Query([
            'post_status' => 'publish',
            'post_type' => $this->wpEntityBundle(),
            'posts_per_page' => 1,
            'meta_query' => [
                [
                    'key' => 'company_name',
                    'value' => $this->objectName(),
                ],
            ],
        ]);

        $post = $query->have_posts() ? $query->next_post() : null;

        return $post ? (int)$post->ID : 0;
    }


    /**
     * @return string
     */
    protected function objectName(): string
    {
        return (string)$this->object->post->post_title;
    }

    /**
     * @return string
     */
    protected function objectDescription(): string
    {
        return (string)$this->object->post->post_content;
    }

    /**
     * @return string
     */
    protected function objectMetaDescription(): string
    {
        return (string)($this->object->meta['_yoast_wpseo_metadesc'][0] ?? '');
    }

    /**
     * @return string
     */
    protected function objectExcerpt(): string
    {
        return (string)$this->object->post->post_excerpt;
    }

    /**
     * @return string
     */
    protected function objectSlug(): string
    {
        return (string)$this->object->post->post_name;
    }

    /**
     * @return string
     */
    protected function objectPublishDate(): string
    {
        return (string)$this->object->post->post_date;
    }

    /**
     * @return string
     */
    protected function objectModifiedDate(): string
    {
        return (string)$this->object->post->post_modified;
    }

    /**
     * @return string
     */
    protected function objectEventOrigin(): string
    {
        return (string)($this->object->meta['_EventOrigin'][0] ?? '');
    }

    /**
     * @return array
     */
    protected function objectEventOrganizerIds(): array
    {
        $ids = [];
        foreach ($this->object->meta as $key => $value) {
            if ($key === '_EventOrganizerID') {
                $ids[] = $value;
            }
        }

        return $ids ?? [];
    }

    /**
     * @return string
     */
    protected function objectThumbnailId(): string
    {
        return (string)($this->object->meta['_thumbnail_id'][0] ?? '');
    }

    /**
     * @return string
     */
    protected function objectEventStartDate(): string
    {
        return (string)($this->object->meta['_EventStartDate'][0] ?? '');
    }

    /**
     * @return string
     */
    protected function objectEventEndDate(): string
    {
        return (string)($this->object->meta['_EventEndDate'][0] ?? '');
    }

    /**
     * @return string
     */
    protected function objectEventTimezone(): string
    {
        return (string)($this->object->meta['_EventTimezone'][0] ?? '');
    }

    /**
     * @return string
     */
    protected function objectEventTimezoneAbbr(): string
    {
        return (string)($this->object->meta['_EventTimezoneAbbr'][0] ?? '');
    }

    /**
     * @return string
     */
    protected function objectEventStartDateUTC(): string
    {
        return (string)($this->object->meta['_EventStartDateUTC'][0] ?? '');
    }

    /**
     * @return string
     */
    protected function objectEventEndDateUTC(): string
    {
        return (string)($this->object->meta['_EventEndDateUTC'][0] ?? '');
    }

    /**
     * @return string
     */
    protected function objectUrl(): string
    {
        return (string)($this->object->meta['_EventURL'][0] ?? '');
    }

    /**
     * @return string
     */
    protected function objectVenueName(): string
    {
        return (string)($this->object->venue['post']->post_title ?? '');
    }

    /**
     * @return string
     */
    protected function objectVenueAddress(): string
    {
        return (string)($this->object->venue['meta']['_VenueAddress'][0] ?? '');
    }

    /**
     * @return string
     */
    protected function objectVenueCity(): string
    {
        return (string)($this->object->venue['meta']['_VenueCity'][0] ?? '');
    }

    /**
     * @return string
     */
    protected function objectVenueCountry(): string
    {
        return (string)($this->object->venue['meta']['_VenueCountry'][0] ?? '');
    }

    /**
     * @return string
     */
    protected function objectVenueProvince(): string
    {
        return (string)($this->object->venue['meta']['_VenueProvince'][0] ?? '');
    }

    /**
     * @return string
     */
    protected function objectVenueState(): string
    {
        return (string)($this->object->venue['meta']['_VenueState'][0] ?? '');
    }

    /**
     * @return string
     */
    protected function objectVenueZip(): string
    {
        return (string)($this->object->venue['meta']['_VenueZip'][0] ?? '');
    }

    /**
     * @return string
     */
    protected function objectVenuePhone(): string
    {
        return (string)($this->object->venue['meta']['_VenuePhone'][0] ?? '');
    }

    /**
     * @return string
     */
    protected function objectVenueURL(): string
    {
        return (string)($this->object->venue['meta']['_VenueURL'][0] ?? '');
    }

    /**
     * @return string
     */
    protected function objectVenueStateProvince(): string
    {
        return (string)($this->object->venue['meta']['_VenueStateProvince'][0] ?? '');
    }

    /**
     * @return array
     */
    protected function objectSponsorIds(): array
    {
        return $this->objectEventOrganizerIds();
    }

    /**
     * @return object|null
     */
    protected function objectLocation(): ?object
    {
        return (object)[
            'name' => $this->objectVenueName(),
            'street' => trim(
                sprintf(
                    '%s, %s',
                    $this->objectVenueCity(),
                    $this->objectVenueAddress(),
                )
            ),
            'locality' => null,
            'postal' => $this->objectVenueZip(),
            'region' => $this->objectVenueStateProvince(),
            'country' => $this->objectVenueCountry(),
        ];
    }

    /**
     * @return string|null
     * @throws Exception
     */
    protected function objectVirtualLocation(): ?string
    {
        return $this->objectUrl() ?? ($this->objectVenueURL() ?? ($this->objectSponsorURL() ?? null));
    }

    /**
     * @return string
     */
    protected function objectEventType(): string
    {
        return 'Cybersecurity Conference';
    }

    /**
     * @return string
     */
    protected function objectTopic(): string
    {
        return 'Uncategorized';
    }

    /**
     * @return string
     */
    protected function objectStatus(): string
    {
        return $this->object->post->post_status ?? '';
    }

    /**
     * @return string
     * @throws Exception
     */
    protected function objectSponsorURL(): string
    {
        $sponsorMappings = $this->getSponsorMappings();

        $sponsorsUrls = [];
        foreach ($sponsorMappings as $sponsor) {
            $url = get_field(
                CompanyProfileCT::GROUP_COMPANY_PROFILE_ADVANCED__FIELD_WEBSITE_URL,
                $sponsor->postId,
                false
            ) ?: '';
            if (empty($url)) {
                continue;
            }
            $sponsorsUrls[] = $url;
        }

        return $sponsorsUrls[0] ?? '';
    }
}
