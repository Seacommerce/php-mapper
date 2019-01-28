<?php


namespace Seacommerce\Mapper;


interface RegistryInterface
{
    public function register(string $source, string $dest) : MappingInterface;

    public function has(string $source, string $dest) : bool;

    public function get(string $source, string $dest) : ?MappingInterface;
}