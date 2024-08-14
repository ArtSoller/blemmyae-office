<?php

/**
 * @licence proprietary
 *
 * @author Eugene Yakovenko <yakoveka@gmail.com>
 */

declare(strict_types=1);

namespace Cra\Integrations\WebhookMessenger;

use ValueError;

/**
 * Class which uniquely identifies a webhook object id.
 */
class ConsumerObjectId
{
    use ValidatorTrait;

    public int|string $id;

    public string $type;

    public string $vendor;

    /**
     * Construct ConsumerObjectId from raw data.
     *
     * @param string $vendor
     * @param string $type
     * @param int|string $id
     */
    public function __construct(string $vendor, string $type, int|string $id)
    {
        $this->setVendor($vendor);
        $this->setType($type);
        $this->setId($id);
    }

    /**
     * Get object as a string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return json_encode($this->asDataObject());
    }

    /**
     * Get instance as plain data object.
     *
     * @return object
     */
    public function asDataObject(): object
    {
        return (object)[
            'vendor' => $this->vendor,
            'type' => $this->type,
            'id' => $this->id,
        ];
    }

    /**
     * Get object ID as provided by vendor.
     *
     * @return int|string
     */
    public function getId(): int|string
    {
        return $this->id;
    }

    /**
     * @param int|string $id
     *
     * @throws ValueError
     */
    public function setId(int|string $id): void
    {
        $this->validateValue($id, 'id');
        $this->id = $id;
    }

    /**
     * Get object type defined by vendor.
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @throws ValueError
     */
    public function setType(string $type): void
    {
        $this->validateValue($type, 'type');
        $this->type = $type;
    }

    /**
     * Get object vendor.
     *
     * @return string
     */
    public function getVendor(): string
    {
        return $this->vendor;
    }

    /**
     * @param string $vendor
     *
     * @throws ValueError
     */
    public function setVendor(string $vendor): void
    {
        $this->validateValue($vendor, 'vendor');
        $this->vendor = $vendor;
    }
}
