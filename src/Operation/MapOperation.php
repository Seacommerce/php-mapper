<?php

namespace Seacommerce\Mapper\Operation;

class MapOperation implements OperationInterface
{
    /**
     * @var string
     */
    private $from;

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
}