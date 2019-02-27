<?php

namespace Seacommerce\Mapper\ValueConverter;

use DateTime;
use DateTimeImmutable;

class DateTimeImmutableConverter
{
    /**
     * @return callable
     */
    public static function toMutable(): callable
    {
        return function (?DateTimeImmutable $value): ?DateTime {
            if ($value === null) {
                return null;
            }
            return (new DateTime())->setTimezone($value->getTimezone())->setTimestamp($value->getTimestamp());
        };
    }

    /**
     * @return callable
     */
    public static function toTimestamp(): callable
    {
        return function (?DateTimeImmutable $value): ?int {
            if ($value === null) {
                return null;
            }
            return $value->getTimestamp();
        };
    }

    /**
     * @return callable
     */
    public static function fromTimestamp(): callable
    {
        return function (?int $value): ?DateTimeImmutable {
            if ($value === null) {
                return null;
            }
            return DateTimeImmutable::createFromMutable((new DateTime())->setTimestamp($value));
        };
    }
}