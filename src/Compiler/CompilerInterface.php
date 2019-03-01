<?php

namespace Seacommerce\Mapper\Compiler;

use Seacommerce\Mapper\ConfigurationInterface;

interface CompilerInterface
{
    public function compile(ConfigurationInterface $configuration): void;
}