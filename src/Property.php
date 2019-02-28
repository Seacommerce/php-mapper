<?php


namespace Seacommerce\Mapper;


use Symfony\Component\PropertyInfo\Type;

class Property
{
    /** @var string */
    private $name;

    /** @var Type[]|null */
    private $types;

    /** @var PropertyReadAccessor|null */
    private $readAccessor;

    /** @var PropertyWriteAccessor|null */
    private $writeAccessor;

    /**
     * Property constructor.
     * @param string $name
     * @param Type[] $types
     * @param PropertyReadAccessor|null $readAccessor
     * @param PropertyWriteAccessor|null $writeAccessor
     */
    public function __construct(string $name, ?array $types,
                                ?PropertyReadAccessor $readAccessor,
                                ?PropertyWriteAccessor $writeAccessor)
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
     * @return Type[]|null
     */
    public function getTypes(): ?array
    {
        return $this->types;
    }

    /**
     * @return PropertyReadAccessor|null
     */
    public function getReadAccessor(): ?PropertyReadAccessor
    {
        return $this->readAccessor;
    }

    /**
     * @return PropertyWriteAccessor|null
     */
    public function getWriteAccessor(): ?PropertyWriteAccessor
    {
        return $this->writeAccessor;
    }

    public function isReadable(): bool
    {
        return $this->readAccessor !== null;
    }

    public function isWritable(): bool
    {
        return $this->writeAccessor !== null;
    }
}