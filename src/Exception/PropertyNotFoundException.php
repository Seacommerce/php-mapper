<?php


namespace Seacommerce\Mapper\Exception;

class PropertyNotFoundException extends \Exception
{
    /**
     * @var string
     */
    private $property;
    /**
     * @var array
     */
    private $existing;

    /**
     * PropertyNotFoundException constructor.
     * @param string $sourceClass
     * @param string[] $existing
     */
    public function __construct(string $sourceClass, array $existing)
    {
        $this->property = $sourceClass;
        $this->existing = $existing;
        $properties = join(', ', $existing);
        parent::__construct("Property '{$sourceClass}' does not exist. Available members: {$properties}.", 0, null);
    }

    /**
     * @return string
     */
    public function getProperty(): string
    {
        return $this->property;
    }

    /**
     * @return array
     */
    public function getExisting(): array
    {
        return $this->existing;
    }
}