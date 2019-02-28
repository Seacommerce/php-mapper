<?php


namespace Seacommerce\Mapper;

class PropertyWriteAccessor
{
    public const ACCESS_TYPE_PROPERTY = 1;
    public const ACCESS_TYPE_METHOD = 2;
    public const ACCESS_TYPE_MAGIC = 3;

    /**
     * @var string
     */
    private $name;
    /**
     * @var int|string
     */
    private $type;

    /**
     * PropertyAccess constructor.
     * @param string $name
     * @param int $type
     */
    public function __construct(string $name, int $type)
    {
        $this->name = $name;
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return PropertyWriteAccessor
     */
    public function setName(string $name): PropertyWriteAccessor
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return int|string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param int|string $type
     * @return PropertyWriteAccessor
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }
}