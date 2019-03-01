<?php

namespace Seacommerce\Mapper;

interface MapperInterface
{
    public function getRegistry(): RegistryInterface;

    public function map($source, $target, array $bag = null);

    public function mapMultiple(iterable $source, string $target, array $bag = null): iterable;

    public function compile(): void;
}