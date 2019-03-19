<?php


namespace Seacommerce\Mapper;


use Seacommerce\Mapper\Exception\PropertyNotFoundException;
use Seacommerce\Mapper\Exception\ValidationErrorsException;
use Seacommerce\Mapper\Extractor\DefaultPropertyExtractor;

class PreparedConfiguration
{
    /** @var ConfigurationInterface */
    private $configuration;

    /** @var Property[] */
    private $sourceProperties;

    /** @var Property[] */
    private $targetProperties;

    /** @var OperationInterface[] */
    private $operations;

    /**
     * PreparedConfiguration constructor.
     * @param ConfigurationInterface $configuration
     */
    public function __construct(ConfigurationInterface $configuration)
    {
        $this->configuration = $configuration;

        $this->operations = $this->configuration->getOperations();
        $this->sourceProperties = $this->extractProperties($this->configuration->getSourceClass());
        $this->targetProperties = $this->extractProperties($this->configuration->getTargetClass());

        if ($this->configuration->getAutoMap()) {
            $matching = array_keys(array_intersect_key($this->sourceProperties, $this->targetProperties));
            $unmapped = array_diff($matching, array_keys($this->operations));
            foreach ($unmapped as $p) {
                $this->operations[$p] = Operation::fromProperty($p);
            }
        }
        if ($this->configuration->getIgnoreUnmapped()) {
            $unmapped = array_diff(array_keys($this->targetProperties), array_keys($this->operations));
            foreach ($unmapped as $p) {
                $this->operations[$p] = Operation::ignore();
            }
        }
    }

    /**
     * @return ConfigurationInterface
     */
    public function getConfiguration(): ConfigurationInterface
    {
        return $this->configuration;
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

    /**
     * @return OperationInterface[]
     */
    public function getOperations(): array
    {
        return $this->operations;
    }

    /**
     * @param bool $throw
     * @return ValidationErrorsException
     * @throws ValidationErrorsException
     * @throws PropertyNotFoundException
     */
    public function validate(bool $throw = true): ?ValidationErrorsException
    {
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
                    throw new PropertyNotFoundException($operation->getFrom(), array_keys($this->sourceProperties));
                }

                if (!$this->sourceProperties[$operation->getFrom()]->isReadable()) {
                    $errors[] = "Source property '{$operation->getFrom()}' is not readable. Either declare a getter/hasser/isser or make the property public.";
                }
            }
        }

        if (empty($errors)) {
            return null;
        }
        $ex = new ValidationErrorsException($this->configuration->getSourceClass(), $this->configuration->getTargetClass(), $errors);
        if ($throw) {
            throw $ex;
        }
        return $ex;
    }

    /**
     * @param string $class
     * @return Property[]
     */
    private function extractProperties(string $class): array
    {
        $extractor = new DefaultPropertyExtractor();
        $s = $extractor->getProperties($class);
        return $s;
    }
}