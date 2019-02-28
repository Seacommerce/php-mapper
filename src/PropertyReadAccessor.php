<?php


namespace Seacommerce\Mapper;

class PropertyReadAccessor
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
     * @return int|string
     */
    public function getType()
    {
        return $this->type;
    }
}