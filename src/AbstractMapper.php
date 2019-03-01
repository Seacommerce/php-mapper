<?php


namespace Seacommerce\Mapper;


abstract class AbstractMapper
{
    /** @var ConfigurationInterface|null */
    private $configuration;

    /** @var OperationInterface[] */
    private $operations = [];

    /** @var RegistryInterface|null */
    private $registry;

    /**
     * @return ConfigurationInterface|null
     */
    public function getConfiguration(): ?ConfigurationInterface
    {
        return $this->configuration;
    }

    /**
     * @param ConfigurationInterface|null $configuration
     * @return AbstractMapper
     */
    public function setConfiguration(?ConfigurationInterface $configuration): AbstractMapper
    {
        $this->configuration = $configuration;
        return $this;
    }

    /**
     * @return OperationInterface[]
     */
    public function getOperations(): array
    {
        return $this->operations;
    }

    /**
     * @param OperationInterface[] $operations
     * @return AbstractMapper
     */
    public function setOperations(array $operations): AbstractMapper
    {
        $this->operations = $operations;
        return $this;
    }

    public function getOperation(string $property): OperationInterface
    {
        return $this->operations[$property];
    }

    /**
     * @return RegistryInterface|null
     */
    public function getRegistry(): ?RegistryInterface
    {
        return $this->registry;
    }

    /**
     * @param RegistryInterface|null $registry
     * @return AbstractMapper
     */
    public function setRegistry(?RegistryInterface $registry): AbstractMapper
    {
        $this->registry = $registry;
        return $this;
    }

    public function getValueConverter($fromType, $toType) {
        return $this->registry->getValueConverter($fromType, $toType);
    }
}