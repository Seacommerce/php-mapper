<?php


namespace Seacommerce\Mapper;


class Context
{
    /** @var RegistryInterface */
    private $registry;

    /** @var ConfigurationInterface */
    private $configuration;

    /** @var array */
    private $bag = [];

    /**
     * Context constructor.
     * @param RegistryInterface $registry
     * @param ConfigurationInterface $configuration
     * @param array $bag
     */
    public function __construct(RegistryInterface $registry, ConfigurationInterface $configuration, array $bag)
    {
        $this->registry = $registry;
        $this->configuration = $configuration;
        $this->bag = $bag;
    }

    /**
     * @return RegistryInterface
     */
    public function getRegistry(): RegistryInterface
    {
        return $this->registry;
    }

    /**
     * @return ConfigurationInterface
     */
    public function getConfiguration(): ConfigurationInterface
    {
        return $this->configuration;
    }

    /**
     * @return array
     */
    public function getBag(): array
    {
        return $this->bag;
    }
}