<?php

namespace Seacommerce\Mapper;

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

    public function getHash(): array
    {
        return [self::class];
    }
}