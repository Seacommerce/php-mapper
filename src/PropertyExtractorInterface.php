<?php


namespace Seacommerce\Mapper;


interface PropertyExtractorInterface
{
    public function getProperties(string $class) : array;
}