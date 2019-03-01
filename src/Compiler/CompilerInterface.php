<?php

namespace Seacommerce\Mapper\Compiler;

use PhpParser\Node;
use Seacommerce\Mapper\ConfigurationInterface;

interface CompilerInterface
{
    public function compile(ConfigurationInterface $configuration): Node;
}