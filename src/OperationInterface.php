<?php


namespace Seacommerce\Mapper;


interface OperationInterface
{
    public function map(string $property, $source, $dest, Context $context);
}