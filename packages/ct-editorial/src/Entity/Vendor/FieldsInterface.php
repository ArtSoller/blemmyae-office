<?php

declare(strict_types=1);

namespace Cra\CtEditorial\Entity\Vendor;

interface FieldsInterface
{
    /**
     * Get "unique ID" field name.
     *
     * @return string
     */
    public function uniqueIdFieldName(): string;

    /**
     * Get unique ID value.
     *
     * @return string
     */
    public function uniqueId(): string;

    /**
     * Set vendor fields' values.
     *
     * @param array $values
     *
     * @return $this
     * @throws \Exception
     */
    public function set(array $values): self;

    /**
     * Get repeater array which is going to be used for saving into DB.
     *
     * @see \Cra\CtEditorial\Entity\Editorial::saveAcfFields()
     */
    public function repeaterArray(): array;
}
