<?php


namespace Seacommerce\Mapper\Events;

use Symfony\Contracts\EventDispatcher\Event;


class PostResolveEvent extends Event
{
    /** @var string */
    private $sourceClass;

    /** @var string */
    private $targetClass;

    /**
     * PostResolveEvent constructor.
     * @param string $sourceClass
     * @param string $targetClass
     */
    public function __construct(string $sourceClass, string $targetClass)
    {
        $this->sourceClass = $sourceClass;
        $this->targetClass = $targetClass;
    }

    /**
     * @return string
     */
    public function getSourceClass(): string
    {
        return $this->sourceClass;
    }

    /**
     * @param string $sourceClass
     * @return PostResolveEvent
     */
    public function setSourceClass(string $sourceClass): PostResolveEvent
    {
        $this->sourceClass = $sourceClass;
        return $this;
    }

    /**
     * @return string
     */
    public function getTargetClass(): string
    {
        return $this->targetClass;
    }

    /**
     * @param string $targetClass
     * @return PostResolveEvent
     */
    public function setTargetClass(string $targetClass): PostResolveEvent
    {
        $this->targetClass = $targetClass;
        return $this;
    }
}