<?php

/**
 * @author  Konstantin Gusev <guvkon.net@icloud.com>
 * @license proprietary
 */

declare(strict_types=1);

namespace Cra\WebhookConsumer\Mapper\Vendor\Ppworks;

use Cra\CtPeople\PeopleCT;
use Cra\Integrations\WebhookMessenger\ConsumerObjectId;
use Cra\Integrations\WebhookMessenger\EntityId;
use Cra\WebhookConsumer\Logger;
use Cra\WebhookConsumer\Mapper\AbstractWordpressWebhookMapper;
use Cra\WebhookConsumer\Mapper\MediaTrait;
use Cra\WebhookConsumer\Mapper\PeoplePostTrait;
use Cra\WebhookConsumer\WebhookMessageHandler;
use Exception;
use Scm\Tools\WpCore;

/**
 * Webhook mapper for saving into person content type.
 */
class Person extends AbstractWordpressWebhookMapper
{
    use PeoplePostTrait;
    use MediaTrait;

    public const TYPE__HOST = 'host';

    public const TYPE__GUEST = 'guest';

    /**
     * @inheritDoc
     */
    public function upsert(ConsumerObjectId $id, int $timestamp, object $object): EntityId
    {
        $this->postId = $this->upsertPersonPost($this->webhookMappingWithFallback($id), $timestamp);
        // We use Source of Sync as an indicator for Swoogo sync that it's a multiple sync person profile.
        // Otherwise, PPWorks takes priority for overriding the main fields.
        $this->updateSourceOfSync();

        try {
            $this->updateAcfFields();
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
    protected function getSyncVendorName(): string
    {
        return WebhookMessageHandler::VENDOR__PPWORKS;
    }

    /**
     * Update ACF fields of the post.
     *
     * @return void
     * @throws Exception
     */
    private function updateAcfFields(): void
    {
        // First name.
        $this->updateAcfField(
            PeopleCT::GROUP_PEOPLE_ADVANCED__FIELD_FIRST_NAME,
            $this->objectFirstName(),
        );
        $this->updateAcfField(
            PeopleCT::GROUP_PPWORKS_PEOPLE_ADVANCED__FIELD_PPWORKS_FIRST_NAME,
            $this->objectFirstName(),
        );

        // Middle name.
        $this->updateAcfField(
            PeopleCT::GROUP_PEOPLE_ADVANCED__FIELD_MIDDLE_NAME,
            $this->objectMiddleName(),
        );
        $this->updateAcfField(
            PeopleCT::GROUP_PPWORKS_PEOPLE_ADVANCED__FIELD_PPWORKS_MIDDLE_NAME,
            $this->objectMiddleName(),
        );

        // Last name.
        $this->updateAcfField(
            PeopleCT::GROUP_PEOPLE_ADVANCED__FIELD_LAST_NAME,
            $this->objectLastName(),
        );
        $this->updateAcfField(
            PeopleCT::GROUP_PPWORKS_PEOPLE_ADVANCED__FIELD_PPWORKS_LAST_NAME,
            $this->objectLastName(),
        );

        // Job title.
        $this->updateJobTitle();

        // Company.
        $companyPostId = $this->objectCompany() ? $this->upsertCompanyPost() : null;
        if ($companyPostId && $this->isSourceOfSyncMatches()) {
            $this->updateCompaniesField($companyPostId);
        }
        $this->updateAcfField(
            PeopleCT::GROUP_PPWORKS_PEOPLE_ADVANCED__FIELD_PPWORKS_COMPANY,
            $companyPostId,
        );

        // Bio.
        $this->updateAcfField(
            PeopleCT::GROUP_PEOPLE_ADVANCED__FIELD_BIO,
            $this->objectBio(),
        );
        $this->updateAcfField(
            PeopleCT::GROUP_PPWORKS_PEOPLE_ADVANCED__FIELD_PPWORKS_BIO,
            $this->objectBio(),
        );

        // Skype.
        $this->updateAcfField(
            PeopleCT::GROUP_PPWORKS_PEOPLE_ADVANCED__FIELD_PPWORKS_SKYPE,
            $this->objectSkype(),
        );

        // Website.
        $this->updateAcfField(
            PeopleCT::GROUP_PEOPLE_ADVANCED__FIELD_WEBSITE,
            $this->objectWebsite(),
        );
        $this->updateAcfField(
            PeopleCT::GROUP_PPWORKS_PEOPLE_ADVANCED__FIELD_PPWORKS_WEBSITE,
            $this->objectWebsite(),
        );

        // Twitter.
        $this->updateAcfField(
            PeopleCT::GROUP_PEOPLE_ADVANCED__FIELD_TWITTER,
            $this->objectTwitter(),
        );
        $this->updateAcfField(
            PeopleCT::GROUP_PPWORKS_PEOPLE_ADVANCED__FIELD_PPWORKS_TWITTER,
            $this->objectTwitter(),
        );

        // Discord.
        $this->updateAcfField(
            PeopleCT::GROUP_PEOPLE_ADVANCED__FIELD_DISCORD,
            $this->objectDiscord(),
        );
        $this->updateAcfField(
            PeopleCT::GROUP_PPWORKS_PEOPLE_ADVANCED__FIELD_PPWORKS_DISCORD,
            $this->objectDiscord(),
        );

        // Instagram.
        $this->updateAcfField(
            PeopleCT::GROUP_PEOPLE_ADVANCED__FIELD_INSTAGRAM,
            $this->objectInstagram(),
        );
        $this->updateAcfField(
            PeopleCT::GROUP_PPWORKS_PEOPLE_ADVANCED__FIELD_PPWORKS_INSTAGRAM,
            $this->objectInstagram(),
        );

        // LinkedIn.
        $this->updateAcfField(
            PeopleCT::GROUP_PEOPLE_ADVANCED__FIELD_LINKEDIN,
            $this->objectLinkedin(),
        );
        $this->updateAcfField(
            PeopleCT::GROUP_PPWORKS_PEOPLE_ADVANCED__FIELD_PPWORKS_LINKEDIN,
            $this->objectLinkedin(),
        );

        // Facebook.
        $this->updateAcfField(
            PeopleCT::GROUP_PEOPLE_ADVANCED__FIELD_FACEBOOK,
            $this->objectFacebook(),
        );
        $this->updateAcfField(
            PeopleCT::GROUP_PPWORKS_PEOPLE_ADVANCED__FIELD_PPWORKS_FACEBOOK,
            $this->objectFacebook(),
        );

        // Mastodon.
        $this->updateAcfField(
            PeopleCT::GROUP_PEOPLE_ADVANCED__FIELD_MASTODON,
            $this->objectMastodon(),
        );
        $this->updateAcfField(
            PeopleCT::GROUP_PPWORKS_PEOPLE_ADVANCED__FIELD_PPWORKS_MASTODON,
            $this->objectMastodon(),
        );

        // Bluesky.
        $this->updateAcfField(
            PeopleCT::GROUP_PEOPLE_ADVANCED__FIELD_BLUESKY,
            $this->objectBluesky(),
        );
        $this->updateAcfField(
            PeopleCT::GROUP_PPWORKS_PEOPLE_ADVANCED__FIELD_PPWORKS_BLUESKY,
            $this->objectBluesky(),
        );

        // Threads.
        $this->updateAcfField(
            PeopleCT::GROUP_PEOPLE_ADVANCED__FIELD_THREADS,
            $this->objectThreads(),
        );
        $this->updateAcfField(
            PeopleCT::GROUP_PPWORKS_PEOPLE_ADVANCED__FIELD_PPWORKS_THREADS,
            $this->objectThreads(),
        );

        // Github.
        $this->updateAcfField(
            PeopleCT::GROUP_PEOPLE_ADVANCED__FIELD_GITHUB,
            $this->objectGithub(),
        );
        $this->updateAcfField(
            PeopleCT::GROUP_PPWORKS_PEOPLE_ADVANCED__FIELD_PPWORKS_GITHUB,
            $this->objectGithub(),
        );

        // Headshot.
        $this->updateHeadshotField();
    }

    /**
     * Update job title field.
     *
     * @return void
     * @throws Exception
     */
    private function updateJobTitle(): void
    {
        WpCore::setPostTerms(
            PeopleCT::TAXONOMY__JOB_TITLE,
            $this->getJobTitleTerm($this->objectJobTitle()),
            $this->postId,
            PeopleCT::GROUP_PPWORKS_PEOPLE_ADVANCED__FIELD_PPWORKS_JOB_TITLE,
        );
    }

    /**
     * Update headshot field.
     */
    private function updateHeadshotField(): void
    {
        $imageId = $this->updateImageField(
            $this->postId,
            $this->objectHeadshot(),
            PeopleCT::GROUP_PPWORKS_PEOPLE_ADVANCED__FIELD_PPWORKS_HEADSHOT,
            "Headshot for {$this->objectFirstName()} {$this->objectLastName()} from PPWorks"
        );

        if (!empty($imageId)) {
            $this->updateAcfField(PeopleCT::GROUP_PEOPLE_ADVANCED__FIELD_HEADSHOT, $imageId);
        }
    }

    /**
     * @inheritDoc
     */
    protected function objectFirstName(): string
    {
        return trim((string)($this->object->firstname ?? ''));
    }

    /**
     * @inheritDoc
     */
    protected function objectMiddleName(): string
    {
        return trim((string)($this->object->middlename ?? ''));
    }

    /**
     * @inheritDoc
     */
    protected function objectLastName(): string
    {
        return trim((string)($this->object->lastname ?? ''));
    }

    /**
     * @inheritDoc
     */
    protected function objectJobTitle(): string
    {
        return trim((string)($this->object->title ?? ''));
    }

    /**
     * @inheritDoc
     */
    protected function objectCompany(): string
    {
        return trim((string)($this->object->company ?? ''));
    }

    /**
     * Get object bio.
     *
     * @return string
     */
    private function objectBio(): string
    {
        return (string)($this->object->bio ?? '');
    }

    /**
     * Get object skype.
     *
     * @return string
     */
    private function objectSkype(): string
    {
        return (string)($this->object->skype ?? '');
    }

    /**
     * Get object website.
     *
     * @return string
     */
    private function objectWebsite(): string
    {
        return (string)($this->object->website ?? '');
    }

    /**
     * Get object twitter.
     *
     * @return string
     */
    private function objectTwitter(): string
    {
        return (string)($this->object->twitter ?? '');
    }

    /**
     * Get object discord.
     *
     * @return string
     */
    private function objectDiscord(): string
    {
        return (string)($this->object->discord ?? '');
    }

    /**
     * Get object instagram.
     *
     * @return string
     */
    private function objectInstagram(): string
    {
        return (string)($this->object->instagram ?? '');
    }

    /**
     * Get object linkedin.
     *
     * @return string
     */
    private function objectLinkedin(): string
    {
        return (string)($this->object->linkedin ?? '');
    }

    /**
     * Get object facebook.
     *
     * @return string
     */
    private function objectFacebook(): string
    {
        return (string)($this->object->facebook ?? '');
    }

    /**
     * Get object mastodon.
     *
     * @return string
     */
    private function objectMastodon(): string
    {
        return (string)($this->object->mastodon ?? '');
    }

    /**
     * Get object bluesky.
     *
     * @return string
     */
    private function objectBluesky(): string
    {
        return (string)($this->object->bluesky ?? '');
    }

    /**
     * Get object threads.
     *
     * @return string
     */
    private function objectThreads(): string
    {
        return (string)($this->object->threads ?? '');
    }

    /**
     * Get object github.
     *
     * @return string
     */
    private function objectGithub(): string
    {
        return (string)($this->object->github ?? '');
    }

    /**
     * Get object headshot.
     *
     * @return string
     */
    private function objectHeadshot(): string
    {
        return (string)($this->object->headshot ?? '');
    }
}
