<?php

namespace Seacommerce\Mapper;

interface ConfigurationInterface
{
    /**
     * @return RegistryInterface|null
     */
    public function getRegistry(): ?RegistryInterface;

    /**
     * @param RegistryInterface|null $registry
     */
    public function setRegistry(?RegistryInterface $registry): void;

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
     * @return string
     */
    public function getHash(): string;

    /**
     * Auto map all unmapped properties.
     * @return ConfigurationInterface
     */
    public function autoMap(): ConfigurationInterface;

    public function getAutoMap(): bool;

    /**
     * Ignore all properties that have been not been mapped yet.
     * This is especially useful for testing and should be avoided on production.
     * @return ConfigurationInterface
     */
    public function ignoreUnmapped(): ConfigurationInterface;

    public function getIgnoreUnmapped(): bool;

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

    public function getOperations(): array;

    public function getAllowMapFromSubClass();

    public function allowMapFromSubClass(bool $allow = true): ConfigurationInterface;

    public function prepare(): PreparedConfiguration;
}