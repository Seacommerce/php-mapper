<?php


namespace Seacommerce\Mapper;


use Seacommerce\Mapper\OperationInterface;

class MapFrom implements OperationInterface
{
    /** @var callable */
    private $callback;

    /**
     * Callback constructor.
     * @param callable $callback
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * @return callable
     */
    public function getCallback(): callable
    {
        return $this->callback;
    }
}