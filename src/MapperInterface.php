<?php


namespace Seacommerce\Mapper;


use Seacommerce\Mapper\Compiler\CompilerInterface;

interface MapperInterface
{
    public function getRegistry(): Registry;

    public function getCompiler(): CompilerInterface;

    public function map($source, $target, array $bag = null);

    public function mapMultiple(iterable $source, string $target, array $bag = null): iterable;

    public function compile(): void;
}