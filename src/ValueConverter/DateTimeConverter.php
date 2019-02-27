<?php

namespace Seacommerce\Mapper\ValueConverter;

use DateTime;
use DateTimeImmutable;

class DateTimeConverter
{
    /**
     * @return callable
     */
    public static function toImmutable() : callable
    {
        return function(?DateTime $value) : ?DateTimeImmutable {
            if ($value === null) {
                return null;
            }
            return DateTimeImmutable::createFromMutable($value);
        };
    }

    /**
     * @return callable
     */
    public static function toTimestamp() : callable
    {
        return function(?DateTime $value) : ?int {
            if ($value === null) {
                return null;
            }
            return $value->getTimestamp();
        };
    }

    /**
     * @return callable
     */
    public static function fromTimestamp() : callable
    {
        return function(?int $value) : ?DateTime {
            if ($value === null) {
                return null;
            }
            return (new DateTime())->setTimestamp($value);
        };
    }
}