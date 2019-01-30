<?php

namespace Seacommerce\Mapper\Compiler;

use Seacommerce\Mapper\ConfigurationInterface;

interface CompilerInterface
{
    public function compile(ConfigurationInterface $configuration): void;

    public function getMappingClassName(ConfigurationInterface $configuration): string;

    public function getMappingFullClassName(ConfigurationInterface $configuration): string;
}