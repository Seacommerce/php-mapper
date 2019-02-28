<?php

namespace Seacommerce\Mapper;

use Seacommerce\Mapper\Exception\ValidationErrorsException;

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
     * @return Property[]
     */
    public function getSourceProperties();

    /**
     * @return Property[]
     */
    public function getTargetProperties(): array;

    /**
     * Auto map all unmapped properties.
     * @return ConfigurationInterface
     */
    public function autoMap(): ConfigurationInterface;

    /**
     * Ignore all properties that have been not been mapped yet.
     * This is especially useful for testing and should be avoided on production.
     * @return ConfigurationInterface
     */
    public function ignoreUnmapped(): ConfigurationInterface;

    /**
     * @param string $property
     * @param \Seacommerce\Mapper\OperationInterface|callable $operation
     * @return ConfigurationInterface
     */
    public function forMember(string $property, $operation): ConfigurationInterface;

    /**
     * @param array $properties
     * @param \Seacommerce\Mapper\OperationInterface|callable $operation
     * @return ConfigurationInterface
     */
    public function forMembers(array $properties, $operation): ConfigurationInterface;

    /**
     * @param string ...$property
     * @return ConfigurationInterface
     * @deprecated Use forMember('foo', Operations::ignore()) instead.
     */
    public function ignore(string ... $property): ConfigurationInterface;

    /**
     * @param array $properties
     * @return ConfigurationInterface
     * @deprecated Use forMember('target', Operations::fromProperty('source')) instead.
     */
    public function map(array $properties): ConfigurationInterface;

    /**
     * @param string $property
     * @param callable $callback
     * @return ConfigurationInterface
     * @deprecated Use forMember('foo', Operations::mapFrom($callable)) instead.
     */
    public function callback(string $property, callable $callback): ConfigurationInterface;

    /**
     * @param string $property
     * @param $value
     * @return ConfigurationInterface
     * @deprecated Use forMember('foo', Operations::setTo($val)) instead.
     */
    public function constValue(string $property, $value): ConfigurationInterface;

    /**
     * @param string $property
     * @param OperationInterface $operation
     * @return ConfigurationInterface
     * @deprecated Use forMember('foo', new SomeCustomOperation()) instead.
     */
    public function custom(string $property, OperationInterface $operation): ConfigurationInterface;

    public function validate(bool $throw = true): ?ValidationErrorsException;

    public  function getOperations() : array;

    public function getAllowMapFromSubClass();

    public function allowMapFromSubClass(bool $allow = true): ConfigurationInterface;
}