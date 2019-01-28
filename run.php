<?php

use Seacommerce\Mapper\Context;
use Seacommerce\Mapper\Operations\Ignore;

require_once './vendor/autoload.php';

// Mapping configuration:
// - auto: yes|no
// - property strategy: source|target
// - source:
//      - class: ...
//      -
// - target


class Source
{
    /** @var int */
    private $id;

    /** @var string|null */
    private $name;

    /** @var DateTime|null */
    private $date;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
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
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return DateTime|null
     */
    public function getDate(): ?DateTime
    {
        return $this->date;
    }

    /**
     * @param DateTime|null $date
     */
    public function setDate(?DateTime $date): void
    {
        $this->date = $date;
    }
}


class Target
{
    /** @var int */
    private $id;

    /** @var string|null */
    private $name;

    /** @var DateTime|null */
    private $date;

    /** @var string|null */
    public $nono;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
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
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return DateTime|null
     */
    public function getDate(): ?DateTime
    {
        return $this->date;
    }

    /**
     * @param DateTime|null $date
     */
    public function setDate(?DateTime $date): void
    {
        $this->date = $date;
    }

    /**
     * @return string|null
     */
    public function getNono(): ?string
    {
        return $this->nono;
    }

    /**
     * @param string|null $nono
     */
    public function setNono(?string $nono): void
    {
        $this->nono = $nono;
    }
}

$source = new Source();
$source->setId(1);
$source->setName('H!');
$source->setDate(new DateTime());

$target = new Target();
$target->nono = 'aap nono';

$registry = new \Seacommerce\Mapper\Registry();
$registry->register(Source::class, Target::class)
    ->forMember('nono', Ignore::class);

//print_r($target);