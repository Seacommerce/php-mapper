<?php


namespace Seacommerce\Mapper\Test\Model\GettersSetters;


use DateTime;

class Target
{
    /** @var int|null */
    private $id;

    /** @var string|null */
    private $name;

    /** @var DateTime */
    private $dateTime;

    /** @var string|null */
    private $callback;

    /** @var string|null */
    private $fixed;

    /** @var string|null */
    private $ignore;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return Target
     */
    public function setId(?int $id): Target
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     * @return Target
     */
    public function setName(?string $name): Target
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDateTime(): DateTime
    {
        return $this->dateTime;
    }

    /**
     * @param DateTime $dateTime
     * @return Target
     */
    public function setDateTime(DateTime $dateTime): Target
    {
        $this->dateTime = $dateTime;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCallback(): ?string
    {
        return $this->callback;
    }

    /**
     * @param string|null $callback
     * @return Target
     */
    public function setCallback(?string $callback): Target
    {
        $this->callback = $callback;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFixed(): ?string
    {
        return $this->fixed;
    }

    /**
     * @param string|null $fixed
     * @return Target
     */
    public function setFixed(?string $fixed): Target
    {
        $this->fixed = $fixed;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getIgnore(): ?string
    {
        return $this->ignore;
    }

    /**
     * @param string|null $ignore
     * @return Target
     */
    public function setIgnore(?string $ignore): Target
    {
        $this->ignore = $ignore;
        return $this;
    }
}