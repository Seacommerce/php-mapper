<?php


namespace Seacommerce\Mapper;


use Seacommerce\Mapper\Compiler\CompilerInterface;
use Seacommerce\Mapper\Compiler\PropertyAccessCompiler;
use Seacommerce\Mapper\Exception\ClassNotFoundException;
use Seacommerce\Mapper\Exception\ConfigurationNotFoundException;
use Seacommerce\Mapper\Exception\InvalidArgumentException;

class Mapper implements MapperInterface
{
    /** @var Registry */
    private $registry;
    /**
     * @var CompilerInterface
     */
    private $compiler;

    /**
     * Mapper constructor.
     * @param Registry $registry
     * @param CompilerInterface $compiler
     */
    public function __construct(Registry $registry, CompilerInterface $compiler)
    {
        $this->registry = $registry;
        $this->compiler = $compiler;
    }

    /**
     * @return Registry
     */
    public function getRegistry(): Registry
    {
        return $this->registry;
    }

    /**
     * @return CompilerInterface
     */
    public function getCompiler(): CompilerInterface
    {
        return $this->compiler;
    }

    public function compile(): void
    {
        foreach ($this->registry as $configuration) {
            $this->compileConfiguration($configuration);
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
        $className = $this->compileConfiguration($configuration);
        $mapping = new $className;
        $context = new Context($this->registry, $configuration, $bag ?? []);
        return $mapping->map($source, $target, $this, $context);
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

    private function compileConfiguration(ConfigurationInterface $configuration): string
    {
        $mappingClassName = $this->compiler->getMappingFullClassName($configuration);
        if (!class_exists($mappingClassName, false)) {
            $this->compiler->compile($configuration);
        }
        return $mappingClassName;
    }

    /**
     * @param string|object|array $source
     * @return string
     */
    private function validateSource($source)
    {
        if (is_array($source)) {
            return 'array';
        } else if (is_object($source)) {
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
            if ($source == 'array') {
                return 'array';
            }
            if (!class_exists($source)) {
                throw new ClassNotFoundException($source);
            }
            return $source;
        } elseif (is_object($source)) {
            return get_class($source);
        }
        throw new InvalidArgumentException($source, "Expected an object, an existing class name or 'array'.");
    }
}