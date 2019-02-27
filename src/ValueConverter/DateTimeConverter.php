<?php

namespace Seacommerce\Mapper\ValueConverter;

use DateTime;
use DateTimeImmutable;

class DateTimeConverter
{
    /**
     * @return callable
     */
    public static function toMutable() : callable
    {
        return function(?DateTimeImmutable $value) {
            if ($value === null) {
                return null;
            }
            return (new DateTime())->setTimezone($value->getTimezone())->setTimestamp($value->getTimestamp());
        };
    }

    /**
     * @return callable
     */
    public static function toImmutable() : callable
    {
        return function(?DateTime $value) {
            if ($value === null) {
                return null;
            }
            return DateTimeImmutable::createFromMutable($value);
        };
    }
}