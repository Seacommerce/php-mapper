<?php

namespace Seacommerce\Mapper;

class Ignore implements OperationInterface
{
    public function getHash(): array
    {
        return [self::class];
    }
}