<?php

/**
 * @licence proprietary
 *
 * @author Konstantin Gusev <guvkon.net@icloud.com>
 */

namespace Cra\Integrations\WebhookMessenger;

use ValueError;

/**
 * Basic validator trait for messages.
 */
trait ValidatorTrait
{
    /**
     * Validate if value is empty.
     *
     * @param mixed $value
     * @param string $fieldName
     *
     * @throws ValueError
     */
    protected function validateValue($value, string $fieldName): void
    {
        if (empty($value)) {
            throw new ValueError("Missing or empty parameter '$fieldName'.");
        }
    }
}
