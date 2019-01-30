<?php


namespace Seacommerce\Mapper\Extractor;


interface PropertyExtractorInterface
{
    public function getProperties(string $class) : array;
}