<?php


namespace Seacommerce\Mapper\Compiler;


use Seacommerce\Mapper\ConfigurationInterface;

interface LoaderInterface
{
    public function load(ConfigurationInterface $configuration): void;

    public function warmup(ConfigurationInterface $configuration): void;
}