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
     * @return string
     */
    public function getMapperClassName(): string;
    /**
     * @return string
     */
    public function getMapperFullClassName(): string;
    /**
     * @return string
     */
    public function getMapperNamespace(): string;
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

    public function validate(bool $throw = true): ?ValidationErrorsException;

    public function getOperations() : array;

    public function getOperation(string $property) : ?OperationInterface;

    public function getAllowMapFromSubClass();

    public function allowMapFromSubClass(bool $allow = true): ConfigurationInterface;
}