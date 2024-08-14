<?php

/**
 * @author  Konstantin Gusev <konstantin.gusev@cyberriskalliance.com>
 * @license proprietary
 */

namespace Cra\WebhookConsumer\Mapper;

use Cra\CtPeople\PeopleCT;

/**
 * Trait which adds Source of Sync functionality.
 *
 * @see https://cra.myjetbrains.com/youtrack/issue/PORT-2425/PPWorks-host-bios-should-populate-on-hosts-contributor-pages#focus=Comments-4-8196.0-0
 */
trait SourceOfSyncTrait
{
    use AcfTrait;

    private const MULTIPLE_SOURCE_OF_SYNC = 'multiple';

    /**
     * Update Source of Sync field.
     *
     * If missing set to the vendor, if not matches set to "multiple".
     *
     * @return void
     */
    protected function updateSourceOfSync(): void
    {
        $source = $this->getSourceOfSync();
        if (empty($source)) {
            $this->updateAcfField($this->getSourceOfSyncFieldName(), $this->getSyncVendorName());
        } elseif ($source !== $this->getSyncVendorName() && $source !== self::MULTIPLE_SOURCE_OF_SYNC) {
            $this->updateAcfField($this->getSourceOfSyncFieldName(), self::MULTIPLE_SOURCE_OF_SYNC);
        }
    }

    /**
     * Check if Source of Sync matches the vendor.
     *
     * @return bool
     */
    protected function isSourceOfSyncMatches(): bool
    {
        return $this->getSourceOfSync() === $this->getSyncVendorName();
    }

    /**
     * Get Source of Sync.
     *
     * @return string|null
     */
    private function getSourceOfSync(): ?string
    {
        return $this->getAcfField($this->getSourceOfSyncFieldName());
    }

    /**
     * Get Source of Sync machine field name.
     *
     * @return string
     */
    private function getSourceOfSyncFieldName(): string
    {
        // The same field is present in multiple content types.
        // PeopleCT has been chosen just because I've implemented it first.
        return PeopleCT::GROUP_WEBHOOK_SYNC__FIELD_SOURCE_OF_SYNC;
    }

    /**
     * Update ACF field value if it's empty or Source of Sync matches.
     *
     * @param string $fieldName
     * @param mixed $value
     *
     * @return void
     */
    private function updateAcfFieldIfAllowed(string $fieldName, mixed $value): void
    {
        if ($this->isSourceOfSyncMatches() || !$this->getAcfField($fieldName)) {
            $this->updateAcfField($fieldName, $value);
        }
    }

    /**
     * Get sync vendor name.
     *
     * @return string
     */
    abstract protected function getSyncVendorName(): string;
}
