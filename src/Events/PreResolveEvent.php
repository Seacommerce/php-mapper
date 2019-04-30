<?php


namespace Seacommerce\Mapper\Events;


use Symfony\Component\EventDispatcher\Event;

class PreResolveEvent extends Event
{
    /** @var string|object */
    private $source;

    /** @var string|object */
    private $target;

    /** @var string|null */
    private $sourceClass;

    /** @var string|null */
    private $targetClass;

    /**
     * PostResolveEvent constructor.
     * @param object|string $source
     * @param object|string $target
     */
    public function __construct($source, $target)
    {
        $this->source = $source;
        $this->target = $target;
    }

    /**
     * @return object|string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @return object|string
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * @return string|null
     */
    public function getSourceClass(): ?string
    {
        return $this->sourceClass;
    }

    /**
     * @param string|null $sourceClass
     */
    public function setSourceClass(?string $sourceClass): void
    {
        $this->sourceClass = $sourceClass;
    }

    /**
     * @return string|null
     */
    public function getTargetClass(): ?string
    {
        return $this->targetClass;
    }

    /**
     * @param string|null $targetClass
     */
    public function setTargetClass(?string $targetClass): void
    {
        $this->targetClass = $targetClass;
    }
}