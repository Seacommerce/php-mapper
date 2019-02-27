<?php

namespace Seacommerce\Mapper;

use Seacommerce\Mapper\OperationInterface;
use Seacommerce\Mapper\ValueConverter\ValueConverterInterface;

class FromProperty implements OperationInterface
{
    /**
     * @var string
     */
    private $from;
    /**
     * @var ValueConverterInterface|callable|null
     */
    private $converter;

    /**
     * MapOperation constructor.
     * @param string $from
     */
    public function __construct(string $from)
    {
        $this->from = $from;
    }

    /**
     * @return string
     */
    public function getFrom(): string
    {
        return $this->from;
    }

    /**
     * @return ValueConverterInterface|callable|null
     */
    public function getConverter()
    {
        return $this->converter;
    }

    /**
     * @param ValueConverterInterface|callable|null $converter
     * @return FromProperty
     */
    public function useConverter($converter) : FromProperty {
        if($converter !== null && !is_callable($converter) && !($converter instanceof ValueConverterInterface))
        {
            throw new \InvalidArgumentException("Invalid value for 'converter'. Expected null, callable or ValueConverterInterface.");
        }
        $this->converter = $converter;
        return $this;
    }
}