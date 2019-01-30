<?php


namespace Seacommerce\Mapper\Test\Model\GettersSetters;


use DateTime;

class Source
{
    /** @var int|null */
    private $id;

    /** @var string|null */
    private $name;

    /** @var DateTime */
    private $date;

    /** @var string|null */
    private $callback;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return Source
     */
    public function setId(?int $id): Source
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
     * @return Source
     */
    public function setName(?string $name): Source
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDate(): DateTime
    {
        return $this->date;
    }

    /**
     * @param DateTime $date
     * @return Source
     */
    public function setDate(DateTime $date): Source
    {
        $this->date = $date;
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
     * @return Source
     */
    public function setCallback(?string $callback): Source
    {
        $this->callback = $callback;
        return $this;
    }
}