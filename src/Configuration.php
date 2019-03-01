<?php

namespace Seacommerce\Mapper;
class Configuration implements ConfigurationInterface
{
    /** @var RegistryInterface */
    private $registry;
    /*** @var string */
    private $sourceClass;
    /*** @var string */
    private $targetClass;
    /*** @var string */
    private $scope;

    /** @var OperationInterface[] */
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

        $sourceClassNormalized = preg_replace('/\\\\{1}/', '_', $sourceClass);
        $destClassNormalized = preg_replace('/\\\\{1}/', '_', $targetClass);
        $this->mapperClassName = "__{$scope}_{$sourceClassNormalized}_to_{$destClassNormalized}";
        $this->mapperFullClassName = "{$this->mapperNamespace}\\{$this->mapperClassName}";
    }

    /**
     * @return RegistryInterface|null
     */
    public function getRegistry() : ?RegistryInterface {
        return $this->registry;
    }

    /**
     * @param RegistryInterface|null $registry
     */
    public function setRegistry(?RegistryInterface $registry): void
    {
        $this->registry = $registry;
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

    public function getOperations(): array
    {
        return $this->operations;
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

    /**
     * @return string
     * @throws \ReflectionException
     */
    public function getHash(): string
    {
        $hash = '';
        if (!\in_array($this->sourceClass, ['array', \stdClass::class], true)) {
            $reflection = new \ReflectionClass($this->sourceClass);
            $hash .= filemtime($reflection->getFileName());
        }
        if (!\in_array($this->targetClass, ['array', \stdClass::class], true)) {
            $reflection = new \ReflectionClass($this->targetClass);
            $hash .= filemtime($reflection->getFileName());
        }
        $all = [$hash];
        foreach ($this->operations as $property => $operation) {
            $all[$property] = $operation->getHash();
        }
        $s = serialize($all);
        $md5 = md5($s);
        return $md5;
    }

    public function autoMap(): ConfigurationInterface
    {
        $this->_autoMap = true;
        return $this;
    }

    /**
     * @return bool
     */
    public function getAutoMap(): bool
    {
        return $this->_autoMap;
    }

    public function ignoreUnmapped(): ConfigurationInterface
    {
        $this->_ignoreUnmapped = true;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIgnoreUnmapped(): bool
    {
        return $this->_ignoreUnmapped;
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
     * @return PreparedConfiguration
     */
    public function prepare(): PreparedConfiguration
    {
        return new PreparedConfiguration($this);
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