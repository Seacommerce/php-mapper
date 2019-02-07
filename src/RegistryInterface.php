<?php

namespace Seacommerce\Mapper;

use Seacommerce\Mapper\Exception\AggregatedValidationErrorsException;
use Seacommerce\Mapper\Exception\ValidationErrorsException;
use IteratorAggregate;

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
     * Validate all the registrered configurations.
     * @param bool $throw
     * @return AggregatedValidationErrorsException|null
     */
    public function validate(bool $throw = true): ?AggregatedValidationErrorsException;
}