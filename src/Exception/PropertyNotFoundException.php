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
     * @param string $property
     * @param string[] $existing
     */
    public function __construct(string $property, array $existing)
    {
        $this->property = $property;
        $this->existing = $existing;
        $properties = join(', ', $existing);
        parent::__construct("Property '{$property}' does not exist. Available members: {$properties}.", 0, null);
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