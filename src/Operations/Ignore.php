<?php


namespace Seacommerce\Mapper\Operations;


use Seacommerce\Mapper\Context;
use Seacommerce\Mapper\OperationInterface;

class Ignore implements OperationInterface
{
    public function map(string $property, $source, $dest, Context $context)
    {
        return 'aapke';
    }
}