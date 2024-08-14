<?php

/**
 * @license PROPRIETARY
 *
 * @author  Pavel Lovkii <pavel.lovkiy@gmail.com>
 */

declare(strict_types=1);

namespace Cra\WebhookConsumer\Mapper\Vendor\Swoogo;

use Cra\CtPeople\PeopleCT;
use Cra\Integrations\WebhookMessenger\ConsumerObjectId;
use Cra\Integrations\WebhookMessenger\EntityId;
use Cra\WebhookConsumer\Logger;
use Cra\WebhookConsumer\Mapper\AbstractWordpressWebhookMapper;
use Cra\WebhookConsumer\Mapper\MediaTrait;
use Cra\WebhookConsumer\Mapper\PeoplePostTrait;
use Cra\WebhookConsumer\WebhookMapping;
use Cra\WebhookConsumer\WebhookMessageHandler;
use Exception;
use Scm\Tools\WpCore;

/**
 * Swoogo speaker mapper class.
 */
class Speaker extends AbstractWordpressWebhookMapper
{
    use PeoplePostTrait;
    use MediaTrait;

    public const TYPE = 'speaker';

    private const LEADERSHIP_BOARD_TYPES = ['Leadership Board', 'Co-Chair', 'Community Director'];

    /**
     * @inheritDoc
     */
    protected function getSyncVendorName(): string
    {
        return WebhookMessageHandler::VENDOR__SWOOGO;
    }

    /**
     * @inheritDoc
     */
    public function upsert(ConsumerObjectId $id, int $timestamp, object $object): EntityId
    {
        $this->postId = $this->upsertPersonPost($this->webhookMappingWithFallback($id), $timestamp);
        $this->updateSourceOfSync();

        try {
            // First name.
            $this->updateAcfField(
                PeopleCT::GROUP_SWOOGO_SPEAKER_ADVANCED__FIELD_SWOOGO_FIRST_NAME,
                $this->objectFirstName()
            );
            $this->updateAcfFieldIfAllowed(PeopleCT::GROUP_PEOPLE_ADVANCED__FIELD_FIRST_NAME, $this->objectFirstName());

            // Middle name.
            $this->updateAcfField(
                PeopleCT::GROUP_SWOOGO_SPEAKER_ADVANCED__FIELD_SWOOGO_MIDDLE_NAME,
                $this->objectMiddleName()
            );
            $this->updateAcfFieldIfAllowed(
                PeopleCT::GROUP_PEOPLE_ADVANCED__FIELD_MIDDLE_NAME,
                $this->objectMiddleName()
            );

            // Last name.
            $this->updateAcfField(
                PeopleCT::GROUP_SWOOGO_SPEAKER_ADVANCED__FIELD_SWOOGO_LAST_NAME,
                $this->objectLastName()
            );
            $this->updateAcfFieldIfAllowed(PeopleCT::GROUP_PEOPLE_ADVANCED__FIELD_LAST_NAME, $this->objectLastName());

            // Bio.
            $this->updateAcfField(PeopleCT::GROUP_SWOOGO_SPEAKER_ADVANCED__FIELD_SWOOGO_BIO, $this->objectBio());
            $this->updateAcfFieldIfAllowed(PeopleCT::GROUP_PEOPLE_ADVANCED__FIELD_BIO, $this->objectBio());

            // Phone.
            $this->updateAcfField(PeopleCT::GROUP_SWOOGO_SPEAKER_ADVANCED__FIELD_SWOOGO_PHONE, $this->objectPhone());
            $this->updateAcfFieldIfAllowed(PeopleCT::GROUP_PEOPLE_ADVANCED__FIELD_PHONE, $this->objectPhone());

            // Email.
            $this->updateAcfField(PeopleCT::GROUP_SWOOGO_SPEAKER_ADVANCED__FIELD_SWOOGO_EMAIL, $this->objectEmail());
            $this->updateAcfFieldIfAllowed(PeopleCT::GROUP_PEOPLE_ADVANCED__FIELD_EMAIL, $this->objectEmail());

            // Twitter.
            $this->updateAcfField(
                PeopleCT::GROUP_SWOOGO_SPEAKER_ADVANCED__FIELD_SWOOGO_TWITTER,
                $this->objectTwitter()
            );
            $this->updateAcfFieldIfAllowed(PeopleCT::GROUP_PEOPLE_ADVANCED__FIELD_TWITTER, $this->objectTwitter());

            // Job title.
            WpCore::setPostTerms(
                PeopleCT::TAXONOMY__JOB_TITLE,
                $this->getJobTitleTerm($this->objectJobTitle()),
                $this->postId,
                PeopleCT::GROUP_SWOOGO_SPEAKER_ADVANCED__FIELD_SWOOGO_JOB_TITLE,
            );

            // Company.
            $companyPostId = $this->objectCompany() ? $this->upsertCompanyPost() : null;
            $this->updateAcfField(PeopleCT::GROUP_SWOOGO_SPEAKER_ADVANCED__FIELD_SWOOGO_COMPANY, $companyPostId);
            if ($companyPostId) {
                $this->updateCompaniesField($companyPostId);
            }

            // Swoogo hash.
            $this->updateAcfFieldIfEmpty(
                PeopleCT::GROUP_SWOOGO_SPEAKER_ADVANCED__FIELD_SWOOGO_HASH,
                $this->objectSwoogoHash()
            );

            // Swoogo ID.
            $this->updateAcfField(PeopleCT::GROUP_SWOOGO_SPEAKER_ADVANCED__FIELD_SWOOGO_ID, $this->objectSwoogoId());

            // Swoogo direct link.
            $this->updateAcfField(
                PeopleCT::GROUP_SWOOGO_SPEAKER_ADVANCED__FIELD_SWOOGO_DIRECT_LINK,
                $this->objectDirectLink()
            );

            // Swoogo birth date.
            $this->updateAcfField(
                PeopleCT::GROUP_SWOOGO_SPEAKER_ADVANCED__FIELD_SWOOGO_BIRTH_DATE,
                $this->objectBirthDate()
            );

            // People type.
            $this->updatePeopleType();

            // Headshot.
            $this->updateHeadshotField();

            // Regions collection entry.
            $this->updateRegionsCollectionEntry();
        } catch (Exception $exception) {
            // It's not critical if some fields haven't been updated.
            (new Logger())->warning(
                sprintf(
                    'Unable to update ACF fields for People post %s: %s',
                    $this->postId,
                    $exception->getMessage()
                )
            );
        }

        $this->publishThisPost();

        return $this->getThisPostEntityId();
    }

    /**
     * @inheritDoc
     */
    protected function allowWpEntityDeletion(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    protected function doSoftEntityDeletion(WebhookMapping $mapping): void
    {
        $this->postId = $mapping->postId;

        // Clear all Swoogo fields.
        $this->updateAcfField(PeopleCT::GROUP_SWOOGO_SPEAKER_ADVANCED__FIELD_SWOOGO_FIRST_NAME, null);
        $this->updateAcfField(PeopleCT::GROUP_SWOOGO_SPEAKER_ADVANCED__FIELD_SWOOGO_MIDDLE_NAME, null);
        $this->updateAcfField(PeopleCT::GROUP_SWOOGO_SPEAKER_ADVANCED__FIELD_SWOOGO_LAST_NAME, null);
        $this->updateAcfField(PeopleCT::GROUP_SWOOGO_SPEAKER_ADVANCED__FIELD_SWOOGO_ID, null);
        $this->updateAcfField(PeopleCT::GROUP_SWOOGO_SPEAKER_ADVANCED__FIELD_SWOOGO_BIO, null);
        $this->updateAcfField(PeopleCT::GROUP_SWOOGO_SPEAKER_ADVANCED__FIELD_SWOOGO_COMPANY, null);
        $this->updateAcfField(PeopleCT::GROUP_SWOOGO_SPEAKER_ADVANCED__FIELD_SWOOGO_JOB_TITLE, null);
        $this->updateAcfField(PeopleCT::GROUP_SWOOGO_SPEAKER_ADVANCED__FIELD_SWOOGO_PHONE, null);
        $this->updateAcfField(PeopleCT::GROUP_SWOOGO_SPEAKER_ADVANCED__FIELD_SWOOGO_EMAIL, null);
        $this->updateAcfField(PeopleCT::GROUP_SWOOGO_SPEAKER_ADVANCED__FIELD_SWOOGO_TWITTER, null);
        $this->updateAcfField(PeopleCT::GROUP_SWOOGO_SPEAKER_ADVANCED__FIELD_SWOOGO_DIRECT_LINK, null);
        $this->updateAcfField(PeopleCT::GROUP_SWOOGO_SPEAKER_ADVANCED__FIELD_SWOOGO_BIRTH_DATE, null);
        $this->updateAcfField(PeopleCT::GROUP_SWOOGO_SPEAKER_ADVANCED__FIELD_SWOOGO_HEADSHOT, null);
        $this->updateAcfField(PeopleCT::GROUP_SWOOGO_SPEAKER_ADVANCED__FIELD_SWOOGO_HASH, null);
        $this->updateAcfField(
            PeopleCT::GROUP_SWOOGO_SPEAKER_ADVANCED__FIELD_REGIONS_COLLECTION,
            []
        );
        WpCore::setPostTerms(PeopleCT::TAXONOMY__SWOOGO_SPEAKER_TYPE, [], $this->postId);
        WpCore::setPostTerms(PeopleCT::TAXONOMY__COMMUNITY_REGION, [], $this->postId);
    }

    /**
     * @inheritDoc
     */
    protected function alternativeFindPersonPostId(): int
    {
        // Check if there is a person synced from Swoogo already.
        return WpCore::searchPostId([
            'post_status' => WpCore::POST_STATUS_PUBLISH,
            'post_type' => $this->wpEntityBundle(),
            'meta_query' => [
                [
                    'key' => 'swoogo_id',
                    'value' => $this->objectSwoogoId(),
                ],
            ],
        ]);
    }

    /**
     * Updates 'Person' post type post 'People Type' ACF field.
     * Adds 'Speaker' term to current terms for all speakers.
     * Adds corresponding subtype of 'Industry Figure' to Leadership persons in addition.
     * Attaches these terms to the post.
     *
     * @return void
     * @throws Exception
     */
    private function updatePeopleType(): void
    {
        $regionsCollection = $this->objectRegionsCollection();

        $types = [];
        foreach ($regionsCollection as $regionItem) {
            if (
                empty($regionItem->speakerType) ||
                !in_array($regionItem->speakerType, self::LEADERSHIP_BOARD_TYPES, true)
            ) {
                continue;
            }
            $types[] = WpCore::getTermByName(PeopleCT::TAXONOMY__PEOPLE_TYPE, $regionItem->speakerType)->term_id;
        }

        $types[] = PeopleCT::TERM__SPEAKER__ID;

        WpCore::setPostTerms(
            PeopleCT::TAXONOMY__PEOPLE_TYPE,
            $types,
            $this->postId,
            PeopleCT::GROUP_PEOPLE_TAXONOMY__FIELD_TYPE,
        );
    }

    /**
     * Updates 'headshot' field (both for 'People Advanced' and 'Swoogo Speaker Advanced' ACF field groups).
     *
     * @return void
     * @throws Exception
     */
    private function updateHeadshotField(): void
    {
        $imageId = $this->updateImageField(
            $this->postId,
            $this->objectHeadshotUrl(),
            PeopleCT::GROUP_SWOOGO_SPEAKER_ADVANCED__FIELD_SWOOGO_HEADSHOT,
            "Headshot for {$this->objectFirstName()} {$this->objectLastName()} from Swoogo"
        );

        if (!empty($imageId)) {
            $this->updateAcfFieldIfAllowed(PeopleCT::GROUP_PEOPLE_ADVANCED__FIELD_HEADSHOT, $imageId);
        }
    }

    /**
     * Update "Regions Collection" field and set terms for "Speaker Type" and "Community Region".
     *
     * @return void
     * @throws Exception
     */
    private function updateRegionsCollectionEntry(): void
    {
        // Update Regions Collection field.
        $eventRegionIds = [];
        $speakerTypeIds = [];

        /** @var array<array{'field_62ff74e44f382': int[], 'field_62ff74e74f383': int[]}> $regionsCollection */
        $regionsCollection = [];
        foreach ($this->objectRegionsCollection() as $regionItem) {
            $eventRegionId = !empty($regionItem->eventRegion)
                ? WpCore::getTermByName(PeopleCT::TAXONOMY__COMMUNITY_REGION, $regionItem->eventRegion)->term_id
                // phpcs:ignore Generic.Files.LineLength.TooLong
                : WpCore::getTermByName(PeopleCT::TAXONOMY__COMMUNITY_REGION, PeopleCT::COMMUNITY_REGION__TERM__UNCATEGORIZED)->term_id;
            $eventRegionIds[] = $eventRegionId;

            $speakerTypeId = !empty($regionItem->speakerType)
                ? WpCore::getTermByName(PeopleCT::TAXONOMY__SWOOGO_SPEAKER_TYPE, $regionItem->speakerType)->term_id
                : null;
            if ($speakerTypeId) {
                $speakerTypeIds[] = $speakerTypeId;
            }

            $regionsCollection[] = [
                // phpcs:ignore Generic.Files.LineLength.TooLong
                PeopleCT::GROUP_SWOOGO_SPEAKER_ADVANCED__FIELD_REGIONS_COLLECTION__SUBFIELD_SWOOGO_COMMUNITY_REGION => $eventRegionId,
                // phpcs:ignore Generic.Files.LineLength.TooLong
                PeopleCT::GROUP_SWOOGO_SPEAKER_ADVANCED__FIELD_REGIONS_COLLECTION__SUBFIELD_SWOOGO_SPEAKER_TYPE => $speakerTypeId,
            ];
        }
        $this->updateAcfField(
            PeopleCT::GROUP_SWOOGO_SPEAKER_ADVANCED__FIELD_REGIONS_COLLECTION,
            $regionsCollection
        );

        // Set "Speaker Type" and "Community Region" terms on the post.
        WpCore::setPostTerms(
            PeopleCT::TAXONOMY__COMMUNITY_REGION,
            array_values(array_unique($eventRegionIds)),
            $this->postId
        );
        WpCore::setPostTerms(
            PeopleCT::TAXONOMY__SWOOGO_SPEAKER_TYPE,
            array_values(array_unique($speakerTypeIds)),
            $this->postId
        );
    }

    /**
     * @return string
     */
    private function objectSwoogoId(): string
    {
        return (string)($this->object->swoogoId ?? '');
    }

    /**
     * @inheritDoc
     */
    protected function objectFirstName(): string
    {
        return trim((string)($this->object->firstName ?? ''));
    }

    /**
     * @inheritDoc
     */
    protected function objectMiddleName(): string
    {
        return trim((string)($this->object->middleName ?? ''));
    }

    /**
     * @inheritDoc
     */
    protected function objectLastName(): string
    {
        return trim((string)($this->object->lastName ?? ''));
    }

    /**
     * @inheritDoc
     */
    protected function objectJobTitle(): string
    {
        return trim($this->object->jobTitle ?? '');
    }

    /**
     * @inheritDoc
     */
    protected function objectCompany(): string
    {
        return trim((string)($this->object->company ?? ''));
    }

    /**
     * @return string
     */
    private function objectBio(): string
    {
        return (string)($this->object->bio ?? '');
    }

    /**
     * @return string
     */
    private function objectPhone(): string
    {
        return (string)($this->object->phone ?? '');
    }

    /**
     * @return string
     */
    private function objectEmail(): string
    {
        return (string)($this->object->email ?? '');
    }

    /**
     * @return string
     */
    private function objectTwitter(): string
    {
        return !empty($this->object->twitterHandle) ?
            sprintf('https://twitter.com/%s', $this->object->twitterHandle) : '';
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
    private function objectBirthDate(): string
    {
        return (string)($this->object->birthDate ?? '');
    }

    private function objectHeadshotUrl(): string
    {
        return (string)($this->object->headshotUrl ?? '');
    }

    /**
     * @return array<object{speakerType?: string, eventRegion?: string}>
     */
    private function objectRegionsCollection(): array
    {
        $regionsCollection = $this->object->regionsCollection ?? [];
        // @phpstan-ignore-next-line
        return is_array($regionsCollection) ?
            array_map(static fn(mixed $collection) => (object)$collection, $regionsCollection) :
            [];
    }

    /**
     * @return string
     */
    private function objectSwoogoHash(): string
    {
        return (string)($this->object->swoogoHash ?? '');
    }
}
