<?php

/**
 * @author  Konstantin Gusev <konstantin.gusev@cyberriskalliance.com>
 * @license proprietary
 */

namespace Cra\WebhookConsumer\Mapper;

use DateTime;
use DateTimeZone;
use Exception;

/**
 * Trait which provides date & time related methods.
 */
trait DateTimeTrait
{
    /**
     * Convert Swoogo event date object into DateTime.
     *
     * @param null|object{date?: ?string, timezone?: ?string}|array{'date'?: ?string, 'timezone'?: ?string} $dateTime
     *
     * @return DateTime|null
     * @throws Exception
     */
    protected function convertToDateTime(mixed $dateTime): ?DateTime
    {
        if (empty($dateTime)) {
            return null;
        }
        $dateTime = (object)$dateTime;
        if (empty($dateTime->date) || empty($dateTime->timezone)) {
            return null;
        }
        return new DateTime($dateTime->date, new DateTimeZone($dateTime->timezone));
    }
}
