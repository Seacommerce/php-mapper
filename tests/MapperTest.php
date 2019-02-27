<?php
declare(strict_types=1);

namespace Seacommerce\Mapper\Test;

use DateTime;
use Seacommerce\Mapper\Compiler\PropertyAccessCompiler;
use Seacommerce\Mapper\Exception\ClassNotFoundException;
use Seacommerce\Mapper\Exception\ConfigurationNotFoundException;
use Seacommerce\Mapper\Exception\InvalidArgumentException;
use Seacommerce\Mapper\Mapper;
use Seacommerce\Mapper\Operation;
use Seacommerce\Mapper\Registry;
use Seacommerce\Mapper\ValueConverter\DateTimeConverter;

class MapperTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @throws ClassNotFoundException
     * @throws ConfigurationNotFoundException
     */
    public function testMissingConfigurationThrowsException()
    {
        $this->expectException(ConfigurationNotFoundException::class);
        $registry = new Registry();
        $mapper = new Mapper($registry, new PropertyAccessCompiler('./var/cache'));
        $mapper->map(new Model\PublicFields\Source(), Model\PublicFields\Target::class);
    }

    /**
     * @throws ClassNotFoundException
     * @throws ConfigurationNotFoundException
     */
    public function testNonObjectOrArrayAsSourceThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        $registry = new Registry();
        $mapper = new Mapper($registry, new PropertyAccessCompiler('./var/cache'));
        $mapper->map(1, Model\PublicFields\Target::class);
    }

    /**
     * @throws ClassNotFoundException
     * @throws ConfigurationNotFoundException
     */
    public function testNonExistingClassNameAsTargetThrowsException()
    {
        $this->expectException(ClassNotFoundException::class);
        $registry = new Registry();
        $mapper = new Mapper($registry, new PropertyAccessCompiler('./var/cache'));
        $mapper->map(new Model\PublicFields\Source(), 'NonExistingClass');
    }

    /**
     * @throws \Exception
     */
    public function testCompile()
    {
        $registry = new Registry();
        $registry->add(Model\PublicFields\Source::class, Model\PublicFields\Target::class)
            ->automap()
            ->forMembers(['ignore', 'dateTime', 'callback', 'fixed'], Operation::ignore())
            ->validate();

        $mapper = new Mapper($registry, new PropertyAccessCompiler('./var/cache'));
        $mapper->compile();

        foreach ($mapper->getRegistry() as $configuration) {
            $className = $mapper->getCompiler()->getMappingFullClassName($configuration);
            echo $className . PHP_EOL;
            $exists = class_exists($className, false);
            $this->assertTrue($exists);
        }
    }


    /**
     * @throws ClassNotFoundException
     * @throws ConfigurationNotFoundException
     * @throws \Exception
     */
    public function testMapToClass()
    {
        $registry = new Registry();
        $registry->add(Model\PublicFields\Source::class, Model\PublicFields\Target::class)
            ->automap()
            ->forMember('ignore', Operation::ignore())
            ->forMember('dateTime', Operation::fromProperty('date'))
            ->forMember('callback', Operation::mapFrom(function () {
                return 'x';
            }))
            ->forMember('fixed', Operation::setTo(100))
            ->validate();

        $mapper = new Mapper($registry, new PropertyAccessCompiler('./var/cache'));


        $source = new Model\PublicFields\Source();
        $source->id = 1;
        $source->name = "Sil";
        $source->date = new DateTime();

        /** @var Model\PublicFields\Target $target */
        $target = $mapper->map($source, Model\PublicFields\Target::class);

        $this->assertNotNull($target);
        $this->assertInstanceOf(Model\PublicFields\Target::class, $target);
        $this->assertNull($target->ignore);
        $this->assertEquals($source->id, $target->id);
        $this->assertEquals($source->name, $target->name);
        $this->assertEquals($source->date, $target->dateTime);
        $this->assertEquals('x', $target->callback);
        $this->assertEquals(100, $target->fixed);
    }

    /**
     * @throws ClassNotFoundException
     * @throws ConfigurationNotFoundException
     * @throws \Exception
     */
    public function testMapToSubclass()
    {
        $registry = new Registry();
        $registry->add(Model\GettersSetters\Source::class, Model\GettersSetters\Target::class)
            ->automap()
            ->allowMapFromSubClass()
            ->forMember('dateMutable', Operation::fromProperty('dateImmutable')->useConverter(DateTimeConverter::toMutable()))
            ->forMember('ignore', Operation::ignore())
            ->forMember('dateTime', Operation::fromProperty('date'))
            ->forMember('callback', Operation::mapFrom(function () {
                return 'x';
            }))
            ->forMember('fixed', Operation::setTo(100))
            ->validate();


        $mapper = new Mapper($registry, new PropertyAccessCompiler('./var/cache'));

        $source = new Model\GettersSetters\SourceSubclass();
        $source->setId(1);
        $source->setName("Sil");
        $source->setDate(new DateTime());
        $source->setDateImmutable(new \DateTimeImmutable());

        /** @var Model\GettersSetters\Target $target */
        $target = $mapper->map($source, Model\GettersSetters\Target::class);

        $this->assertNotNull($target);
        $this->assertInstanceOf(Model\GettersSetters\Target::class, $target);
        $this->assertNull($target->getIgnore());
        $this->assertEquals($source->getId(), $target->getId());
        $this->assertEquals($source->getName(), $target->getName());
        $this->assertEquals($source->getDate(), $target->getDateTime());
        $this->assertEquals('x', $target->getCallback());
        $this->assertEquals(100, $target->getFixed());
        $this->assertInstanceOf(DateTime::class, $target->getDateMutable());
    }

    /**
     * @throws \Exception
     */
    public function testValueConverters()
    {
        $registry = new Registry();
        $registry->add(Model\ValueConverter\Source::class, Model\ValueConverter\Target::class)
            ->forMember('date', Operation::fromProperty('date')->useConverter(DateTimeConverter::toImmutable()))
            ->validate();

        $source = new Model\ValueConverter\Source();
        $source->setDate(new DateTime());

        $mapper = new Mapper($registry, new PropertyAccessCompiler('./var/cache'));
        /** @var Model\ValueConverter\Target $target */
        $target = $mapper->map($source, Model\ValueConverter\Target::class);

        $this->assertNotNull($target);
        $this->assertEquals($source->getDate()->getTimestamp(), $target->getDate()->getTimestamp());
        $this->assertEquals($source->getDate()->getTimezone()->getName(), $target->getDate()->getTimezone()->getName());
    }

    /**
     * @throws \Exception
     */
    public function testDefaultValueConverters()
    {
        $registry = new Registry();
        $registry->registerDefaultValueConverters();
        $registry->add(Model\ValueConverter\Source::class, Model\ValueConverter\Target::class)
            ->automap()
            ->validate();

        $source = new Model\ValueConverter\Source();
        $source->setDate(new DateTime());

        $mapper = new Mapper($registry, new PropertyAccessCompiler('./var/cache'));
        /** @var Model\ValueConverter\Target $target */
        $target = $mapper->map($source, Model\ValueConverter\Target::class);

        $this->assertNotNull($target);
        $this->assertEquals($source->getDate()->getTimestamp(), $target->getDate()->getTimestamp());
        $this->assertEquals($source->getDate()->getTimezone()->getName(), $target->getDate()->getTimezone()->getName());
    }
}