<?php


namespace Seacommerce\Mapper;

class Mapping implements MappingInterface
{
    /*** @var string */
    private $sourceClass;
    /*** @var string */
    private $targetClass;

    /** @var array */
    private $sourceProperties = [];
    /** @var array */
    private $targetProperties = [];

    /** @var array */
    private $operations = [];

    public function __construct(string $sourceClass, string $targetClass)
    {
        $this->sourceClass = $sourceClass;
        $this->targetClass = $targetClass;
        $this->extractProperties();
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

    public function auto(): self
    {
        return $this;
    }

    /**
     * @param string $member
     * @param $operation
     * @return Mapping
     * @throws \Exception
     */
    public function forMember(string $member, $operation): MappingInterface
    {
        if(!isset($this->targetProperties[$member])) {
            $members = join(', ', array_keys($this->targetProperties));
            throw new \Exception("Member '{$member}' does not exist. Available members: {$members}");
        }

        // Full class name allowed when it's implements
        if (is_string($operation)) {
            if (class_exists($operation, true)) {
                $operation = new $operation;
            }
        }

        if ($operation instanceof OperationInterface) {
            $this->operations[$member] = $operation;
            return $this;
        }

        if (is_callable($operation)) {
            $this->operations[$member] = $operation;
            return $this;
        }

        // TODO: Specific exception
        throw new \Exception("Invalid value for 'operation'. OperationInterface or callable expected.");
    }

    private function extractProperties(): void
    {
        $extractor = new DefaultPropertyExtractor();
        $this->sourceProperties = array_flip($extractor->getProperties($this->sourceClass));
        $this->targetProperties = array_flip($extractor->getProperties($this->targetClass));
    }
}