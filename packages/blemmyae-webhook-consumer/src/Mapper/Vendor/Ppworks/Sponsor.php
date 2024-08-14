<?php

/**
 * @author  Konstantin Gusev <guvkon.net@icloud.com>
 * @license proprietary
 */

declare(strict_types=1);

namespace Cra\WebhookConsumer\Mapper\Vendor\Ppworks;

use Cra\CtCompanyProfile\CompanyProfileCT;
use Cra\Integrations\WebhookMessenger\ConsumerObjectId;
use Cra\Integrations\WebhookMessenger\EntityId;
use Cra\WebhookConsumer\Logger;
use Cra\WebhookConsumer\Mapper\AbstractWordpressWebhookMapper;
use Cra\WebhookConsumer\Mapper\CompanyProfilePostTrait;
use Cra\WebhookConsumer\WebhookMessageHandler;
use Exception;

/**
 * Webhook mapper for saving into company content type.
 */
class Sponsor extends AbstractWordpressWebhookMapper
{
    use CompanyProfilePostTrait;

    public const TYPE = 'sponsor';

    /**
     * @inheritDoc
     */
    public function upsert(ConsumerObjectId $id, int $timestamp, object $object): EntityId
    {
        $this->postId = $this->upsertCompanyPost($this->webhookMappingWithFallback($id), $timestamp);
        $this->updateSourceOfSync();

        try {
            $this->updateAcfFields();
        } catch (Exception $exception) {
            // We are ok with partially filled-in company profiles.
            (new Logger())->warning(
                sprintf(
                    'Unable to update ACF fields for Company Profile post %s: %s',
                    $this->postId,
                    $exception->getMessage()
                )
            );
        }

        $this->publishThisPost();

        return $this->getThisPostEntityId();
    }

    /**
     * Update ACF fields of the post.
     *
     * @throws Exception
     */
    private function updateAcfFields(): void
    {
        // Name.
        $this->updateAcfFieldIfAllowed(
            CompanyProfileCT::GROUP_COMPANY_PROFILE_ADVANCED__FIELD_COMPANY_NAME,
            $this->objectName()
        );

        // About.
        $this->updateAcfFieldIfAllowed(
            CompanyProfileCT::GROUP_COMPANY_PROFILE_ADVANCED__FIELD_ABOUT,
            $this->objectDescription()
        );
        $this->updateAcfField(
            CompanyProfileCT::GROUP_PPWORKS_COMPANY_ADVANCED__FIELD_PPWORKS_ABOUT,
            $this->objectDescription()
        );

        // Logo.
        $this->updateLogoField();
    }

    /**
     * Update logo field.
     */
    private function updateLogoField(): void
    {
        $imageId = $this->updateImageField(
            $this->postId,
            $this->objectLogo(),
            CompanyProfileCT::GROUP_PPWORKS_COMPANY_ADVANCED__FIELD_PPWORKS_LOGO,
            "Logo for {$this->objectName()} from PPWorks"
        );

        if (!empty($imageId)) {
            $this->updateAcfFieldIfAllowed(CompanyProfileCT::GROUP_COMPANY_PROFILE_ADVANCED__FIELD_LOGO, $imageId);
        }
    }

    /**
     * @inheritDoc
     */
    private function objectName(): string
    {
        return trim((string)($this->object->name ?? ''));
    }

    /**
     * Get object description.
     *
     * @return string
     */
    private function objectDescription(): string
    {
        return (string)($this->object->description ?? '');
    }

    /**
     * Get object logo.
     *
     * @return string
     */
    private function objectLogo(): string
    {
        return (string)($this->object->logo ?? '');
    }

    /**
     * @inheritDoc
     */
    protected function getSyncVendorName(): string
    {
        return WebhookMessageHandler::VENDOR__PPWORKS;
    }
}
