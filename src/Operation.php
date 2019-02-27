<?php


namespace Seacommerce\Mapper;

class Operation
{
    public static function fromProperty(string $property): FromProperty
    {
        return new FromProperty($property);
    }

    public static function ignore(): Ignore
    {
        return new Ignore();
    }

    public static function mapFrom(callable $callback): MapFrom
    {
        return new MapFrom($callback);
    }

    public static function setTo($value): SetTo
    {
        return new SetTo($value);
    }
}