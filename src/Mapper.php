<?php

namespace Seacommerce\Mapper;

use Seacommerce\Mapper\Compiler\LoaderInterface;
use Seacommerce\Mapper\Events\PostResolveEvent;
use Seacommerce\Mapper\Events\PreResolveEvent;
use Seacommerce\Mapper\Exception\ClassNotFoundException;
use Seacommerce\Mapper\Exception\ConfigurationNotFoundException;
use Seacommerce\Mapper\Exception\InvalidArgumentException;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;

class Mapper implements MapperInterface
{
    /** @var RegistryInterface */
    private $registry;
    /*** @var LoaderInterface */
    private $loader;
    /** @var EventDispatcher */
    private $eventDispatcher = null;

    /**
     * Mapper constructor.
     * @param RegistryInterface $registry
     * @param LoaderInterface $loader
     */
    public function __construct(RegistryInterface $registry, LoaderInterface $loader)
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
     * @return null
     */
    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }

    /**
     * @param null $eventDispatcher
     * @return Mapper
     */
    public function setEventDispatcher($eventDispatcher): self
    {
        $this->eventDispatcher = $eventDispatcher;
        return $this;
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

        list($sourceClass, $targetClass) = $this->resolve($source, $target);

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
        if (!class_exists($name)) {
            $this->loader->load($configuration);
        }
        $class = $configuration->getMapperFullClassName();
        /** @var AbstractMapper $mapper */
        $mapper = new $class;
        $mapper->setRegistry($configuration->getRegistry());
        $mapper->setOperations($configuration->getOperations());
        return $mapper;
    }

    /**
     * @param $source
     * @param $target
     * @return array
     * @throws ClassNotFoundException
     */
    private function resolve($source, $target)
    {
        $preEvent = new PreResolveEvent($source, $target);
        $this->dispatch(MapperEvents::PRE_RESOLVE, $preEvent);

        $sourceClass = $preEvent->getSourceClass() ?? $this->resolveSource($source);
        $targetClass = $preEvent->getTargetClass() ?? $this->resolveTarget($target);
        $postEvent = new PostResolveEvent($sourceClass, $targetClass);
        $this->dispatch(MapperEvents::POST_RESOLVE, $postEvent);
        return [$postEvent->getSourceClass(), $postEvent->getTargetClass()];
    }

    /**
     * @param string|object|array $source
     * @return string
     */
    private function resolveSource($source)
    {
        if (is_array($source)) {
            return 'array';
        } else
            if (is_object($source)) {
                return get_class($source);
            }
        throw new InvalidArgumentException($source, "Expected an object or an array.");
    }

    /**
     * @param string|object $source
     * @return string
     * @throws ClassNotFoundException
     */
    private function resolveTarget($source)
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

    private function dispatch(string $name, Event $event)
    {
        if ($this->eventDispatcher === null) {
            return;
        }
        $this->eventDispatcher->dispatch($event,$name);
    }
}