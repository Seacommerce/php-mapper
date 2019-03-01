<?php

namespace Seacommerce\Mapper;

use Seacommerce\Mapper\Exception\AggregatedValidationErrorsException;
use IteratorAggregate;
use Seacommerce\Mapper\ValueConverter\ValueConverterInterface;

interface RegistryInterface extends IteratorAggregate
{
    /**
     * @return string
     */
    public function getScope(): string;

    /**
     * @param string $source
     * @param string $target
     * @return ConfigurationInterface
     */
    public function add(string $source, string $target): ConfigurationInterface;

    /**
     * @param string $source
     * @param string $target
     * @return bool
     */
    public function has(string $source, string $target): bool;

    /**
     * @param string $source
     * @param string $dest
     * @return ConfigurationInterface|null
     */
    public function get(string $source, string $dest): ?ConfigurationInterface;

    /**
     * @param string $fromType
     * @param string $toType
     * @param ValueConverterInterface|callable $converter
     */
    public function registerValueConverter(string $fromType, string $toType, $converter): void;


    public function registerDefaultValueConverters(): void;

    /**
     * @param string $fromType
     * @param string $toType
     * @return ValueConverterInterface|callable|null
     */
    public function getValueConverter(string $fromType, string $toType);

    /**
     * Validate all the registered configurations.
     * @param bool $throw
     * @return AggregatedValidationErrorsException|null
     */
    public function validate(bool $throw = true): ?AggregatedValidationErrorsException;
}