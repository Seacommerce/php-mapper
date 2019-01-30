<?php

namespace Test\Run;

use DateTime;
use Seacommerce\Mapper\Compiler\PropertyAccessCompiler;
use Seacommerce\Mapper\Context;
use Seacommerce\Mapper\Mapper;
use Seacommerce\Mapper\MapperInterface;
use Seacommerce\Mapper\Operation\ConstValueOperation;
use Seacommerce\Mapper\Registry;

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

    /** @var int|null */
    public $fixed;

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

    /**
     * @return int|null
     */
    public function getFixed(): ?int
    {
        return $this->fixed;
    }

    /**
     * @param int|null $fixed
     * @return Target
     */
    public function setFixed(?int $fixed): Target
    {
        $this->fixed = $fixed;
        return $this;
    }
}

$source = new Source();
$source->setId(1);
$source->setName('H!');
$source->setDate(new DateTime());

$target = new Target();
$target->nono = 'aap nono';

$registry = new Registry();
$registry->add(Source::class, Target::class)
    ->automap()
    ->map(['name' => 'name'])
    ->map(['date' => 'date'])
    ->callback('id', function (string $property, ?Source $source, Target $target, MapperInterface $mapper, Context $context) {
        return 2;
    })
    ->ignore('nono')
    ->custom('fixed', new ConstValueOperation(1));

$registry->validate();

$mapper = new Mapper($registry, new PropertyAccessCompiler('./var/cache'));

foreach (range(0, 5000) as $i) {
    $target = $mapper->map($source, Target::class);
}

print_r($target);