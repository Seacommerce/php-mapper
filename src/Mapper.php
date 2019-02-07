<?php


namespace Seacommerce\Mapper;


use Seacommerce\Mapper\Compiler\PropertyAccessCompiler;
use Seacommerce\Mapper\Exception\ClassNotFoundException;
use Seacommerce\Mapper\Exception\ConfigurationNotFoundException;
use Seacommerce\Mapper\Exception\InvalidArgumentException;

class Mapper implements MapperInterface
{
    /** @var Registry */
    private $registry;
    /**
     * @var PropertyAccessCompiler
     */
    private $compiler;

    /**
     * Mapper constructor.
     * @param Registry $registry
     * @param PropertyAccessCompiler $compiler
     */
    public function __construct(Registry $registry, PropertyAccessCompiler $compiler)
    {
        $this->registry = $registry;
        $this->compiler = $compiler;
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

        $mappingClassName = $this->compiler->getMappingFullClassName($configuration);
        if (!class_exists($mappingClassName, false)) {
            $this->compiler->compile($configuration);
        }
        $mapping = new $mappingClassName;
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