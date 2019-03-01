<?php


namespace Seacommerce\Mapper;


abstract class AbstractMapper
{
    /** @var ConfigurationInterface */
    private $configuration;

    /** @var OperationInterface[] */
    private $operations;

    /**
     * @return ConfigurationInterface
     */
    public function getConfiguration(): ConfigurationInterface
    {
        return $this->configuration;
    }

    /**
     * @param ConfigurationInterface $configuration
     * @return AbstractMapper
     */
    public function setConfiguration(ConfigurationInterface $configuration): AbstractMapper
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
}