<?php

/**
 * @author  Konstantin Gusev <konstantin.gusev@cyberriskalliance.com>
 * @license proprietary
 */

namespace Cra\WebhookConsumer\Mapper;

/**
 * Trait with helper functions for dealing with ACF fields of the main post ID.
 */
trait AcfTrait
{
    protected int $postId;

    /**
     * Get ACF field value.
     *
     * @param string $fieldName
     * @param bool $formatValue
     *
     * @return mixed
     */
    protected function getAcfField(string $fieldName, bool $formatValue = true): mixed
    {
        return get_field($fieldName, $this->postId, $formatValue);
    }

    /**
     * Update ACF field value.
     *
     * @param string $fieldName
     * @param mixed $value
     *
     * @return void
     */
    protected function updateAcfField(string $fieldName, mixed $value): void
    {
        update_field($fieldName, $value, $this->postId);
    }

    /**
     * Update ACF field value if it's empty.
     *
     * @param string $fieldName
     * @param mixed $value
     *
     * @return void
     */
    protected function updateAcfFieldIfEmpty(string $fieldName, mixed $value): void
    {
        if (!$this->getAcfField($fieldName)) {
            $this->updateAcfField($fieldName, $value);
        }
    }
}
