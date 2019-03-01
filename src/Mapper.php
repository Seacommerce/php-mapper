<?php

namespace Seacommerce\Mapper;

use Seacommerce\Mapper\Compiler\LoaderInterface;
use Seacommerce\Mapper\Exception\ClassNotFoundException;
use Seacommerce\Mapper\Exception\ConfigurationNotFoundException;
use Seacommerce\Mapper\Exception\InvalidArgumentException;
use Seacommerce\Mapper\Exception\PropertyNotFoundException;
use Seacommerce\Mapper\Extractor\DefaultPropertyExtractor;

class Mapper implements MapperInterface
{
    /** @var RegistryInterface */
    private $registry;

    private $compiled = [];
    /**
     * @var LoaderInterface
     */
    private $loader;

    /**
     * Mapper constructor.
     * @param RegistryInterface $registry
     * @param LoaderInterface $loader
     */
    public function __construct(RegistryInterface $registry,
                                LoaderInterface $loader)
    {
        $this->registry = $registry;
        $this->loader = $loader;
    }

    /**
     * @return RegistryInterface
     */
    public function getRegistry(): RegistryInterface
    {
        return $this->registry;
    }

    /**
     */
    public function compile(): void
    {
        foreach ($this->registry as $configuration) {
            $this->createMapper($configuration);
        }
    }

    /**
     * @param string|object|array $source
     * @param string|object $target
     * @param array|null $bag
     * @return mixed
     * @throws ClassNotFoundException
     * @throws ConfigurationNotFoundException
     */
    public function map($source, $target, array $bag = null)
    {
        if ($source === null) {
            return null;
        }
        $sourceClass = $this->validateSource($source);
        $targetClass = $this->validateTarget($target);

        if (is_string($target)) {
            $target = new $target;
        }

        $configuration = $this->registry->get($sourceClass, $targetClass);
        if (empty($configuration)) {
            throw new ConfigurationNotFoundException($sourceClass, $targetClass);
        }
        $mapper = $this->createMapper($configuration);
        $context = new Context($this->registry, $configuration, $bag ?? []);
        return $mapper->map($source, $target, $this, $context);
    }

    /**
     * @param iterable $source
     * @param string $target
     * @param array|null $bag
     * @return iterable
     * @throws ClassNotFoundException
     * @throws ConfigurationNotFoundException
     */
    public function mapMultiple(iterable $source, string $target, array $bag = null): iterable
    {
        $list = [];
        foreach ($source as $s) {
            $list[] = $this->map($s, $target, $bag);
        }
        return $list;
    }

    /**
     * @param ConfigurationInterface $configuration
     * @return AbstractMapper
     */
    private function createMapper(ConfigurationInterface $configuration): AbstractMapper
    {
        $name = $configuration->getMapperFullClassName();
        $hash = $configuration->getHash();
        if (!isset($this->compiled[$name]) || $this->compiled[$name] !== $hash) {
            $this->loader->load($configuration);
            $this->compiled[$name] = $hash;
        }
        $class = $configuration->getMapperFullClassName();
        /** @var AbstractMapper $mapper */
        $mapper = new $class;
        $mapper->setOperations($configuration->getOperations());

        return $mapper;
    }

    /**
     * @param string|object|array $source
     * @return string
     */
    private function validateSource($source)
    {
        if (is_array($source)) {
            return 'array';
        } else
            if (is_object($source)) {
                return get_class($source);
            }
        throw new InvalidArgumentException($source, "Expected an object, an array or an existing class name.");
    }

    /**
     * @param string|object $source
     * @return string
     * @throws ClassNotFoundException
     */
    private function validateTarget($source)
    {
        if (is_string($source)) {
            if ($source === 'array') {
                return 'array';
            }
            if (!class_exists($source)) {
                throw new ClassNotFoundException($source);
            }
            return $source;
        } else
            if (is_object($source)) {
                return get_class($source);
            }
        throw new InvalidArgumentException($source, "Expected an object, an existing class name or 'array'.");
    }
}