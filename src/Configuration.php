<?php

namespace Seacommerce\Mapper;

use Seacommerce\Mapper\Exception\InvalidArgumentException;
use Seacommerce\Mapper\Exception\ValidationErrorsException;
use Seacommerce\Mapper\Exception\PropertyNotFoundException;
use Seacommerce\Mapper\Extractor\DefaultPropertyExtractor;
use Seacommerce\Mapper\Operation\CallbackOperation;
use Seacommerce\Mapper\Operation\IgnoreOperation;
use Seacommerce\Mapper\Operation\MapOperation;
use Seacommerce\Mapper\Operation\OperationInterface;
use Seacommerce\Mapper\Operation\ConstValueOperation;

class Configuration implements ConfigurationInterface
{
    /*** @var string */
    private $sourceClass;
    /*** @var string */
    private $targetClass;

    /*** @var string */
    private $scope;

    /** @var array */
    private $sourceProperties = [];
    /** @var array */
    private $targetProperties = [];

    /** @var array */
    private $operations = [];

    public function __construct(string $sourceClass, string $targetClass, string $scope)
    {
        $this->sourceClass = $sourceClass;
        $this->targetClass = $targetClass;
        $this->scope = $scope;
        $this->extractProperties();
    }

    /**
     * @return string
     */
    public function getSourceClass(): string
    {
        return $this->sourceClass;
    }

    /**
     * @return string
     */
    public function getTargetClass(): string
    {
        return $this->targetClass;
    }

    /**
     * @return string
     */
    public function getScope(): string
    {
        return $this->scope;
    }

    public function automap(): ConfigurationInterface
    {
        $matching = array_keys(array_intersect_key($this->sourceProperties, $this->targetProperties));
        $unmapped = array_diff($matching, array_keys($this->operations));
        foreach ($unmapped as $p) {
            $this->operations[$p] = new MapOperation($p);
        }
        return $this;
    }

    /**
     * @param string ...$property
     * @return ConfigurationInterface
     * @throws \Exception
     */
    public function ignore(string ... $property): ConfigurationInterface
    {
        foreach ($property as $p) {
            $this->ensureTargetProperty($p);
            $this->operations[$p] = new IgnoreOperation();
        }
        return $this;
    }

    /**
     * @param array $properties
     * @return ConfigurationInterface
     * @throws \Exception
     */
    public function map(array $properties): ConfigurationInterface
    {
        foreach ($properties as $t => $s) {
            $this->ensureTargetProperty($t);
            $this->ensureSourceProperty($s);
            $this->operations[$t] = new MapOperation($s);
        }
        return $this;
    }

    /**
     * @param string $property
     * @param callable $callback
     * @return ConfigurationInterface
     * @throws \Exception
     */
    public function callback(string $property, callable $callback): ConfigurationInterface
    {
        $this->ensureTargetProperty($property);
        $this->operations[$property] = new CallbackOperation($callback);
        return $this;
    }

    /**
     * @param string $property
     * @param $value
     * @return ConfigurationInterface
     * @throws \Exception
     */
    public function constValue(string $property, $value): ConfigurationInterface
    {
        $this->ensureTargetProperty($property);
        $this->operations[$property] = new ConstValueOperation($value);
        return $this;
    }

    /**
     * @param string $property
     * @param $operation
     * @return Configuration
     * @throws \Exception
     */
    public function custom(string $property, OperationInterface $operation): ConfigurationInterface
    {
        $this->ensureTargetProperty($property);
        if (is_string($operation)) {
            if (class_exists($operation, true)) {
                $this->operations[$property] = new $operation;
                return $this;
            }
        }
        if ($operation instanceof OperationInterface) {
            $this->operations[$property] = $operation;
            return $this;
        }
        if (is_callable($operation)) {
            $this->operations[$property] = $operation;
            return $this;
        }
        // TODO: Specific exception
        throw new \Exception("Invalid value for 'operation'. OperationInterface or callable expected.");
    }

    /**
     * @param bool $throw
     * @return array
     * @throws ValidationErrorsException
     */
    public function validate(bool $throw = true): array
    {
        $diff = array_keys(array_diff_key($this->targetProperties, $this->operations));
        $errors = [];
        foreach ($diff as $property) {
            $errors[] = "Missing mapping for property '{$property}'.";
        }
        if ($throw && !empty($errors)) {
            throw new ValidationErrorsException($errors);
        }
        return $errors;
    }

    public function getOperations(): array
    {
        return $this->operations;
    }

    private function extractProperties(): void
    {
        $extractor = new DefaultPropertyExtractor();
        $s = $extractor->getProperties($this->sourceClass);
        $t = $extractor->getProperties($this->targetClass);
        if (empty($s)) {
            throw new PropertyNotFoundException($this->sourceClass, []);
        }

        if (empty($t)) {
            throw new PropertyNotFoundException($this->targetClass, []);
        }

        $this->sourceProperties = array_flip($s);
        $this->targetProperties = array_flip($t);
    }

    /**
     * @param string $property
     * @throws \Exception
     */
    private function ensureTargetProperty(string $property): void
    {
        if (!isset($this->targetProperties[$property])) {
            throw new PropertyNotFoundException($property, array_keys($this->targetProperties));
        }
    }

    /**
     * @param string $property
     * @throws \Exception
     */
    private function ensureSourceProperty(string $property): void
    {
        if (!isset($this->sourceProperties[$property])) {
            throw new PropertyNotFoundException($property, array_keys($this->sourceProperties));
        }
    }
}