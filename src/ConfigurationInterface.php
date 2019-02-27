<?php

namespace Seacommerce\Mapper;

use Seacommerce\Mapper\Exception\ValidationErrorsException;
use Seacommerce\Mapper\OperationInterface;

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

    /**
     * @return string
     */
    public function getScope(): string;

    /**
     * @return array
     */
    public function getSourceProperties();

    /**
     * @return array
     */
    public function getTargetProperties(): array;

    public function automap(): ConfigurationInterface;

    public function forMember(string $property, OperationInterface $operation): ConfigurationInterface;

    public function forMembers(array $properties, OperationInterface $operation): ConfigurationInterface;

    /**
     * @param string ...$property
     * @return ConfigurationInterface
     * @deprecated Use forMember('foo', Operations::ignore()) instead.
     */
    public function ignore(string ... $property): ConfigurationInterface;

    /**
     * @param array $properties
     * @return ConfigurationInterface
     * @deprecated Use forMember('target', Operations::mapFrom('source')) instead.
     */
    public function map(array $properties): ConfigurationInterface;

    /**
     * @param string $property
     * @param callable $callback
     * @return ConfigurationInterface
     * @deprecated Use forMember('foo', Operations::callback($callable)) instead.
     */
    public function callback(string $property, callable $callback): ConfigurationInterface;

    /**
     * @param string $property
     * @param $value
     * @return ConfigurationInterface
     * @deprecated Use forMember('foo', Operations::const($val)) instead.
     */
    public function constValue(string $property, $value): ConfigurationInterface;

    /**
     * @param string $property
     * @param OperationInterface $operation
     * @return ConfigurationInterface
     * @deprecated Use forMember('foo', new CustomOperation()) instead.
     */
    public function custom(string $property, OperationInterface $operation): ConfigurationInterface;

    public function validate(bool $throw = true): ?ValidationErrorsException;

    public  function getOperations() : array;

    public function getAllowMapFromSubClass();

    public function allowMapFromSubClass(bool $allow = true): ConfigurationInterface;
}