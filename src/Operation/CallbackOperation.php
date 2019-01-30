<?php


namespace Seacommerce\Mapper\Operation;


class CallbackOperation implements OperationInterface
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