<?php

namespace Seacommerce\Mapper;

use Seacommerce\Mapper\Operation\OperationInterface;

interface ConfigurationInterface
{
    /**
     * @return string
     */
    public function getSourceClass(): string;

    /**
     * @return string
     */
    public function getTargetClass(): string;

    public function automap(): ConfigurationInterface;

    public function ignore(string ... $property): ConfigurationInterface;

    public function map(array $properties): ConfigurationInterface;

    public function callback(string $property, callable $callback): ConfigurationInterface;

    public function constValue(string $property, $value): ConfigurationInterface;

    public function custom(string $property, OperationInterface $operation): ConfigurationInterface;

    public function validate(bool $throw = true): array;

    public  function getOperations() : array;
}