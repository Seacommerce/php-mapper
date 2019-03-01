<?php

namespace Seacommerce\Mapper;

class SetTo implements OperationInterface
{
    /** @var mixed */
    private $value;

    /**
     * Set constructor.
     * @param $value mixed
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    public function getHash(): array
    {
        return [self::class];
    }
}