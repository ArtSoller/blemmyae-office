<?php

/**
 * @license PROPRIETARY
 *
 * @author  Pavel Lovkii <pavel.lovkiy@gmail.com>
 */

declare(strict_types=1);

namespace Cra\WebhookConsumer\Mapper\Vendor\Swoogo;

use Cra\CtCompanyProfile\CompanyProfileCT;
use Cra\Integrations\WebhookMessenger\ConsumerObjectId;
use Cra\Integrations\WebhookMessenger\EntityId;
use Cra\WebhookConsumer\Logger;
use Cra\WebhookConsumer\Mapper\AbstractWordpressWebhookMapper;
use Cra\WebhookConsumer\Mapper\CompanyProfilePostTrait;
use Cra\WebhookConsumer\WebhookMessageHandler;
use Exception;

/**
 * Swoogo sponsor mapper class.
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
     * Updates 'Company Profile Advanced' ACF field group fields.
     *
     * @throws Exception
     */
    private function updateAcfFields(): void
    {
        // Name.
        $this->updateAcfField(
            CompanyProfileCT::GROUP_SWOOGO_COMPANY_ADVANCED__FIELD_SWOOGO_NAME,
            $this->objectName()
        );
        $this->updateAcfFieldIfEmpty(
            CompanyProfileCT::GROUP_COMPANY_PROFILE_ADVANCED__FIELD_COMPANY_NAME,
            $this->objectName()
        );

        // About.
        $this->updateAcfField(
            CompanyProfileCT::GROUP_SWOOGO_COMPANY_ADVANCED__FIELD_SWOOGO_ABOUT,
            $this->objectDescription(),
        );
        $this->updateAcfFieldIfEmpty(
            CompanyProfileCT::GROUP_COMPANY_PROFILE_ADVANCED__FIELD_ABOUT,
            $this->objectDescription()
        );

        // Website URL.
        $this->updateAcfField(
            CompanyProfileCT::GROUP_SWOOGO_COMPANY_ADVANCED__FIELD_SWOOGO_URL,
            $this->objectWebsite()
        );
        $this->updateAcfFieldIfEmpty(
            CompanyProfileCT::GROUP_COMPANY_PROFILE_ADVANCED__FIELD_WEBSITE_URL,
            $this->objectWebsite()
        );

        // Swoogo ID.
        $this->updateAcfField(
            CompanyProfileCT::GROUP_SWOOGO_COMPANY_ADVANCED__FIELD_SWOOGO_ID,
            $this->objectSwoogoId()
        );

        // Swoogo direct link.
        $this->updateAcfField(
            CompanyProfileCT::GROUP_SWOOGO_COMPANY_ADVANCED__FIELD_SWOOGO_DIRECT_LINK,
            $this->objectDirectLink()
        );

        // Logo.
        $this->updateLogoField();
    }

    /**
     * Updates 'logo' field (both for 'Company Profile Advanced' and 'Swoogo Company Advanced' ACF field groups).
     *
     * @throws Exception
     */
    private function updateLogoField(): void
    {
        $imageId = $this->updateImageField(
            $this->postId,
            $this->objectLogoUrl(),
            CompanyProfileCT::GROUP_SWOOGO_COMPANY_ADVANCED__FIELD_SWOOGO_LOGO,
            "Logo for {$this->objectName()} company from Swoogo"
        );

        if (!empty($imageId)) {
            $this->updateAcfFieldIfEmpty(CompanyProfileCT::GROUP_COMPANY_PROFILE_ADVANCED__FIELD_LOGO, $imageId);
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
     * @return string
     */
    private function objectSwoogoId(): string
    {
        return (string)($this->object->swoogoId ?? '');
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
    private function objectDirectLink(): string
    {
        return (string)($this->object->directLink ?? '');
    }

    /**
     * @return string
     */
    private function objectWebsite(): string
    {
        return (string)($this->object->website ?? '');
    }

    /**
     * @return string
     */
    private function objectLogoUrl(): string
    {
        // Example -> //assets.swoogo.com/uploads/medium/3486207-65b90775492d7.jpg
        $parts = parse_url($this->object->logoId ?? '');
        if (!$parts || empty($parts['host']) || empty($parts['path'])) {
            return '';
        }

        return "https://{$parts['host']}{$parts['path']}";
    }

    /**
     * @inheritDoc
     */
    protected function getSyncVendorName(): string
    {
        return WebhookMessageHandler::VENDOR__SWOOGO;
    }
}
