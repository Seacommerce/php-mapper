<?php


namespace Seacommerce\Mapper;


use Symfony\Component\PropertyInfo\Type;

class Property
{
    /** @var string */
    private $name;

    /** @var Type[] */
    private $types;

    /** @var PropertyReadAccessor */
    private $readAccessor;

    /** @var PropertyWriteAccessor */
    private $writeAccessor;

    /**
     * Property constructor.
     * @param string $name
     * @param Type[] $types
     * @param PropertyReadAccessor $readAccessor
     * @param PropertyWriteAccessor $writeAccessor
     */
    public function __construct(string $name, array $types, PropertyReadAccessor $readAccessor, PropertyWriteAccessor $writeAccessor)
    {
        $this->name = $name;
        $this->types = $types;
        $this->readAccessor = $readAccessor;
        $this->writeAccessor = $writeAccessor;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return Type[]
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    /**
     * @return PropertyReadAccessor
     */
    public function getReadAccessor(): PropertyReadAccessor
    {
        return $this->readAccessor;
    }

    /**
     * @return PropertyWriteAccessor
     */
    public function getWriteAccessor(): PropertyWriteAccessor
    {
        return $this->writeAccessor;
    }
}