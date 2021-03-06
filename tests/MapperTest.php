<?php

/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedParameterInspection */

declare(strict_types=1);

namespace Seacommerce\Mapper\Test;

use DateTime;
use Seacommerce\Mapper\Compiler\CachedLoader;
use Seacommerce\Mapper\Compiler\NativeCompiler;
use Seacommerce\Mapper\ConfigurationInterface;
use Seacommerce\Mapper\Context;
use Seacommerce\Mapper\Events\PostResolveEvent;
use Seacommerce\Mapper\Events\PreResolveEvent;
use Seacommerce\Mapper\Exception\ClassNotFoundException;
use Seacommerce\Mapper\Exception\ConfigurationNotFoundException;
use Seacommerce\Mapper\Exception\InvalidArgumentException;
use Seacommerce\Mapper\Mapper;
use Seacommerce\Mapper\MapperEvents;
use Seacommerce\Mapper\MapperInterface;
use Seacommerce\Mapper\Operation;
use Seacommerce\Mapper\Registry;
use Seacommerce\Mapper\Test\Model\GettersSetters\Source;
use Seacommerce\Mapper\Test\Model\GettersSetters\Target;
use Seacommerce\Mapper\ValueConverter\DateTimeConverter;
use Seacommerce\Mapper\ValueConverter\DateTimeImmutableConverter;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Filesystem\Filesystem;

class MapperTest extends \PHPUnit\Framework\TestCase
{
    private static $cacheDir;

    /**
     * @throws \ReflectionException
     */
    public static function setUpBeforeClass(): void
    {
        self::$cacheDir = __DIR__ . '/../cache/' . (new \ReflectionClass(__CLASS__))->getShortName();
        (new Filesystem())->remove(self::$cacheDir);
    }

    /**
     * @throws ClassNotFoundException
     * @throws ConfigurationNotFoundException
     * @throws \Exception
     */
    public function testMissingConfigurationThrowsException()
    {
        $this->expectException(ConfigurationNotFoundException::class);
        $registry = new Registry(__FUNCTION__);
        $mapper = new Mapper($registry, new CachedLoader(new NativeCompiler(), self::$cacheDir));
        $mapper->map(new Model\PublicFields\Source(), Model\PublicFields\Target::class);
    }

    /**
     * @throws ClassNotFoundException
     * @throws ConfigurationNotFoundException
     * @throws \Exception
     */
    public function testNonObjectOrArrayAsSourceThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        $registry = new Registry(__FUNCTION__);
        $mapper = new Mapper($registry, new CachedLoader(new NativeCompiler(), self::$cacheDir));
        $mapper->map(1, Model\PublicFields\Target::class);
    }

    /**
     * @throws ClassNotFoundException
     * @throws ConfigurationNotFoundException
     * @throws \Exception
     */
    public function testNonExistingClassNameAsTargetThrowsException()
    {
        $this->expectException(ClassNotFoundException::class);
        $registry = new Registry(__FUNCTION__);
        $mapper = new Mapper($registry, new CachedLoader(new NativeCompiler(), self::$cacheDir));
        $mapper->map(new Model\PublicFields\Source(), 'NonExistingClass');
    }

    /**
     * @throws \Exception
     */
    public function testCompile()
    {
        $registry = new Registry(__FUNCTION__);
        $registry->add(Model\PublicFields\Source::class, Model\PublicFields\Target::class)
            ->autoMap()
            ->forMembers(['ignore', 'dateTime', 'callback', 'fixed'], Operation::ignore())
            ->prepare()
            ->validate();

        $mapper = new Mapper($registry, new CachedLoader(new NativeCompiler(), self::$cacheDir));
        $mapper->compile();

        /** @var ConfigurationInterface $configuration */
        foreach ($mapper->getRegistry() as $configuration) {
            $this->assertTrue(class_exists($configuration->getMapperFullClassName(), false));
        }
    }

    /**
     * @throws ClassNotFoundException
     * @throws ConfigurationNotFoundException
     * @throws \Exception
     */
    public function testMapToClass()
    {
        $registry = new Registry(__FUNCTION__);
        $registry->add(Model\PublicFields\Source::class, Model\PublicFields\Target::class)
            ->autoMap()
            ->forMember('ignore', Operation::ignore())
            ->forMember('dateTime', Operation::fromProperty('date'))
            ->forMember('callback', Operation::mapFrom(function () {
                return 'x';
            }))
            ->forMember('fixed', Operation::setTo(100));

        $mapper = new Mapper($registry, new CachedLoader(new NativeCompiler(), self::$cacheDir));

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
        $registry = new Registry(__FUNCTION__);
        $registry->add(Model\GettersSetters\Source::class, Model\GettersSetters\Target::class)
            ->autoMap()
            ->allowMapFromSubClass()
            ->forMember('dateMutable', Operation::fromProperty('dateImmutable')->useConverter(DateTimeImmutableConverter::toMutable()))
            ->forMember('ignore', Operation::ignore())
            ->forMember('dateTime', Operation::fromProperty('date'))
            ->forMember('callback', Operation::mapFrom(function () {
                return 'x';
            }))
            ->forMember('fixed', Operation::setTo(100))
            ->prepare()
            ->validate();


        $mapper = new Mapper($registry, new CachedLoader(new NativeCompiler(), self::$cacheDir));

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
        $registry = new Registry(__FUNCTION__);
        $registry->add(Model\ValueConverter\Source::class, Model\ValueConverter\Target::class)
            ->forMember('date', Operation::fromProperty('date')->useConverter(DateTimeConverter::toImmutable()))
            ->forMember('time', Operation::fromProperty('time')->useConverter(DateTimeConverter::fromTimestamp()))
            ->prepare()
            ->validate();

        $source = new Model\ValueConverter\Source();
        $source
            ->setDate(new DateTime())
            ->setTime((new DateTime())->getTimestamp());

        $mapper = new Mapper($registry, new CachedLoader(new NativeCompiler(), self::$cacheDir));
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
        $registry = new Registry(__FUNCTION__);
        $registry->registerDefaultValueConverters();
        $registry->add(Model\ValueConverter\Source::class, Model\ValueConverter\Target::class)
            ->autoMap()
            ->prepare()
            ->validate();

        $source = new Model\ValueConverter\Source();
        $source
            ->setDate(new DateTime())
            ->setTime((new DateTime())->getTimestamp());

        $mapper = new Mapper($registry, new CachedLoader(new NativeCompiler(), self::$cacheDir));
        /** @var Model\ValueConverter\Target $target */
        $target = $mapper->map($source, Model\ValueConverter\Target::class);

        $this->assertNotNull($target);
        $this->assertEquals($source->getDate()->getTimestamp(), $target->getDate()->getTimestamp());
        $this->assertEquals($source->getDate()->getTimezone()->getName(), $target->getDate()->getTimezone()->getName());
    }

    /**
     * @throws \Exception
     */
    public function testBefore()
    {
        $registry = new Registry(__FUNCTION__);
        $prepared = $registry->add(Model\GettersSetters\Source::class, Model\GettersSetters\Target::class)
            ->autoMap()
            ->before(function (?Model\GettersSetters\Source $source,
                               Model\GettersSetters\Target $target,
                               MapperInterface $mapperInterface,
                               Context $context) {
                $target->setIgnore("before");
                $target->setFixed("before");
            })
            ->ignoreUnmapped()
            ->forMember('fixed', Operation::setTo('modified'))
            ->prepare();

        $prepared->validate();

        $source = new Model\GettersSetters\Source();
        $mapper = new Mapper($registry, new CachedLoader(new NativeCompiler(), self::$cacheDir));
        /** @var Model\GettersSetters\Target $target */
        $target = $mapper->map($source, Model\GettersSetters\Target::class);
        $this->assertNotNull($target);
        $this->assertSame('before', $target->getIgnore());
        $this->assertSame('modified', $target->getFixed());
    }

    public function testAfter()
    {
        $registry = new Registry(__FUNCTION__);
        $prepared = $registry->add(Model\GettersSetters\Source::class, Model\GettersSetters\Target::class)
            ->autoMap()
            ->after(function (?Model\GettersSetters\Source $source,
                              Model\GettersSetters\Target $target,
                              MapperInterface $mapperInterface,
                              Context $context) {
                $target->setName("after");
            })
            ->ignoreUnmapped()
            ->forMember('id', Operation::setTo(1))
            ->forMember('name', Operation::setTo('mapped'))
            ->prepare();

        $prepared->validate();

        $source = new Model\GettersSetters\Source();
        $mapper = new Mapper($registry, new CachedLoader(new NativeCompiler(), self::$cacheDir));
        /** @var Model\GettersSetters\Target $target */
        $target = $mapper->map($source, Model\GettersSetters\Target::class);
        $this->assertNotNull($target);
        $this->assertSame(1, $target->getId());
        $this->assertSame('after', $target->getName());
    }

    public function testPreResolveEvent()
    {
        $this->expectException(ConfigurationNotFoundException::class);
        $this->expectExceptionMessage("Configuration for mapping from 'NonExistingSource' to 'NonExistingTarget' could not be found.");

        $registry = new Registry(__FUNCTION__);
        $mapper = new Mapper($registry, new CachedLoader(new NativeCompiler(), self::$cacheDir));
        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addListener(MapperEvents::PRE_RESOLVE, function (PreResolveEvent $event) {
            $this->assertInstanceOf(Model\GettersSetters\Source::class, $event->getSource());
            $this->assertEquals(Model\GettersSetters\Target::class, $event->getTarget());
            $this->assertNull($event->getSourceClass());
            $this->assertNull($event->getTargetClass());

            $event->setSourceClass('NonExistingSource');
            $event->setTargetClass('NonExistingTarget');
        });
        $mapper->setEventDispatcher($eventDispatcher);
        $mapper->map(new Model\GettersSetters\Source(), Model\GettersSetters\Target::class);
    }

    public function testPostResolveEvent()
    {
        $this->expectException(ConfigurationNotFoundException::class);
        $this->expectExceptionMessage("Configuration for mapping from 'NonExistingSource' to 'NonExistingTarget' could not be found.");

        $registry = new Registry(__FUNCTION__);
        $mapper = new Mapper($registry, new CachedLoader(new NativeCompiler(), self::$cacheDir));
        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addListener(MapperEvents::POST_RESOLVE, function (PostResolveEvent $event) {
            $this->assertEquals(Model\GettersSetters\Source::class, $event->getSourceClass());
            $this->assertEquals(Model\GettersSetters\Target::class, $event->getTargetClass());

            $event->setSourceClass('NonExistingSource');
            $event->setTargetClass('NonExistingTarget');
        });
        $mapper->setEventDispatcher($eventDispatcher);
        $mapper->map(new Model\GettersSetters\Source(), Model\GettersSetters\Target::class);
    }
}