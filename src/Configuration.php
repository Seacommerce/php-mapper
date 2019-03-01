<?php

namespace Seacommerce\Mapper;

use Seacommerce\Mapper\Exception\ValidationErrorsException;
use Seacommerce\Mapper\Exception\PropertyNotFoundException;
use Seacommerce\Mapper\Extractor\DefaultPropertyExtractor;
use Seacommerce\Mapper\ValueConverter\ValueConverterInterface;
use Symfony\Component\PropertyInfo\Type;

class Configuration implements ConfigurationInterface
{
    /*** @var string */
    private $sourceClass;
    /*** @var string */
    private $targetClass;
    /*** @var string */
    private $scope;

    /** @var Property[] */
    private $sourceProperties = [];
    /** @var Property[] */
    private $targetProperties = [];

    /** @var array */
    private $operations = [];

    /** @var bool */
    private $_allowMapFromSubClass = false;

    /** @var bool */
    private $_autoMap = false;

    /** @var bool */
    private $_ignoreUnmapped = false;

    /*** @var array */
    private $valueConverters;

    /** @var string */
    private $mapperClassName;
    /** @var string */
    private $mapperFullClassName;

    /** @var string */
    private $mapperNamespace = 'Mappings';

    /**
     * Configuration constructor.
     * @param string $sourceClass
     * @param string $targetClass
     * @param string $scope
     * @param array $valueConverters
     */
    public function __construct(string $sourceClass, string $targetClass, string $scope, array $valueConverters = [])
    {
        $this->sourceClass = $sourceClass;
        $this->targetClass = $targetClass;
        $this->scope = $scope;
        $this->valueConverters = $valueConverters;
        $sourceClass = preg_replace('/\\\\{1}/', '_', $sourceClass);
        $destClass = preg_replace('/\\\\{1}/', '_', $targetClass);
        $this->mapperClassName = "__{$scope}_{$sourceClass}_to_{$destClass}";
        $this->mapperFullClassName = "{$this->mapperNamespace}\\{$this->mapperClassName}";
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

    /**
     * @return string
     */
    public function getMapperClassName(): string
    {
        return $this->mapperClassName;
    }

    /**
     * @return string
     */
    public function getMapperFullClassName(): string
    {
        return $this->mapperFullClassName;
    }

    /**
     * @return string
     */
    public function getMapperNamespace(): string
    {
        return $this->mapperNamespace;
    }

    /**
     * @return Property[]
     */
    public function getSourceProperties(): array
    {
        return $this->sourceProperties;
    }

    /**
     * @return Property[]
     */
    public function getTargetProperties(): array
    {
        return $this->targetProperties;
    }

    public function getOperations(): array
    {
        return $this->operations;
    }

    public function getOperation(string $property): ?OperationInterface
    {
        return $this->operations[$property];
    }

    public function allowMapFromSubClass(bool $allow = true): ConfigurationInterface
    {
        $this->_allowMapFromSubClass = $allow;
        return $this;
    }

    public function getAllowMapFromSubClass()
    {
        return $this->_allowMapFromSubClass;
    }

    public function autoMap(): ConfigurationInterface
    {
        $this->_autoMap = true;
        return $this;
    }

    public function ignoreUnmapped(): ConfigurationInterface
    {
        $this->_ignoreUnmapped = true;
        return $this;
    }

    /**
     * @param string $property
     * @param OperationInterface|callable $operation
     * @return ConfigurationInterface
     * @throws \Exception
     */
    public function forMember(string $property, $operation): ConfigurationInterface
    {
        if (is_callable($operation)) {
            $this->operations[$property] = Operation::mapFrom($operation);
        }
        $this->operations[$property] = $operation;
        return $this;
    }

    /**
     * @param array $properties
     * @param \Seacommerce\Mapper\OperationInterface|callable $operation
     * @return ConfigurationInterface
     * @throws \Exception
     */
    public function forMembers(array $properties, $operation): ConfigurationInterface
    {
        foreach ($properties as $p) {
            $this->forMember($p, $operation);
        }
        return $this;
    }

    /**
     * @param bool $throw
     * @return ValidationErrorsException
     * @throws ValidationErrorsException
     * @throws PropertyNotFoundException
     */
    public function validate(bool $throw = true): ?ValidationErrorsException
    {
        $this->extractProperties();
        if ($this->_autoMap) {
            $matching = array_keys(array_intersect_key($this->sourceProperties, $this->targetProperties));
            $unmapped = array_diff($matching, array_keys($this->operations));
            foreach ($unmapped as $p) {
                $this->operations[$p] = Operation::fromProperty($p);
            }
        }
        if ($this->_ignoreUnmapped) {
            $unmapped = array_diff(array_keys($this->targetProperties), array_keys($this->operations));
            foreach ($unmapped as $p) {
                $this->operations[$p] = Operation::ignore();
            }
        }

        $unmapped = array_keys(array_diff_key($this->targetProperties, $this->operations));
        $errors = [];
        foreach ($unmapped as $propertyName) {
            $targetProperty = $this->targetProperties[$propertyName];
            if ($targetProperty->isWritable()) {
                $errors[] = "Missing mapping for property '{$propertyName}'.";
            }
        }
        foreach ($this->operations as $property => $operation) {
            if (!isset($this->targetProperties[$property])) {
                throw new PropertyNotFoundException($property, array_keys($this->targetProperties));
            }

            if (!$this->targetProperties[$property]->isWritable()) {
                $errors[] = "Target property '{$property}' is not writable. Either declare a setter or make the property public.";
            }
            if ($operation instanceof FromProperty) {
                if (!isset($this->sourceProperties[$operation->getFrom()])) {
                    throw new PropertyNotFoundException($property, array_keys($this->sourceProperties));
                }

                if (!$this->sourceProperties[$operation->getFrom()]->isReadable()) {
                    $errors[] = "Source property '{$operation->getFrom()}' is not readable. Either declare a getter/hasser/isser or make the property public.";
                }
            }
        }

        if (empty($errors)) {
            return null;
        }
        $ex = new ValidationErrorsException($this->sourceClass, $this->targetClass, $errors);
        if ($throw) {
            throw $ex;
        }
        return $ex;
    }

    /**
     * @throws PropertyNotFoundException
     */
    private function extractProperties(): void
    {
        $extractor = new DefaultPropertyExtractor();
        $s = $extractor->getProperties($this->sourceClass);
        $t = $extractor->getProperties($this->targetClass);
        if (empty($s)) {
            throw new PropertyNotFoundException('*any*', []);
        }
        if (empty($t)) {
            throw new PropertyNotFoundException('*any*', []);
        }
        $this->sourceProperties = $s;
        $this->targetProperties = $t;
    }

//    /**
//     * @param string $source
//     * @param string $target
//     * @return null|callable|ValueConverterInterface
//     */
//    private function getValueConverter(string $source, string $target)
//    {
//        /** @var Type[] $fromTypes */
//        $fromTypes = $this->sourceProperties[$source]->getTypes();
//        /** @var Type[] $toTypes */
//        $toTypes = $this->targetProperties[$target]->getTypes();
//        if ($fromTypes !== null && $fromTypes !== null && count($fromTypes) !== 1 && count($toTypes) !== 1) {
//            return null;
//        }
//
//        $fromType = array_shift($fromTypes);
//        $toType = array_shift($toTypes);
//
//        $f = $fromType->getClassName() ?? $fromType->getBuiltinType();
//        $t = $toType->getClassName() ?? $toType->getBuiltinType();
//        if ($f === null || $t === null) {
//            return null;
//        }
//
//        $converter = $this->valueConverters[$f][$t] ?? null;
//        return $converter;
//    }
}