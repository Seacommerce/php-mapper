<?php


namespace Seacommerce\Mapper;


interface MappingInterface
{
    public function forMember(string $member, $operation): self;
}