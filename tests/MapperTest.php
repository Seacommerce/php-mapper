<?php
declare(strict_types=1);

namespace Seacommerce\Mapper\Test;

use DateTime;
use Seacommerce\Mapper\Compiler\CachedLoader;
use Seacommerce\Mapper\Compiler\NativeCompiler;
use Seacommerce\Mapper\ConfigurationInterface;
use Seacommerce\Mapper\Exception\ClassNotFoundException;
use Seacommerce\Mapper\Exception\ConfigurationNotFoundException;
use Seacommerce\Mapper\Exception\InvalidArgumentException;
use Seacommerce\Mapper\Mapper;
use Seacommerce\Mapper\Operation;
use Seacommerce\Mapper\Registry;
use Seacommerce\Mapper\ValueConverter\DateTimeConverter;
use Seacommerce\Mapper\ValueConverter\DateTimeImmutableConverter;

class MapperTest extends \PHPUnit\Framework\TestCase
{
    private static $cacheDir;

    /**
     * @throws \ReflectionException
     */
    public static function setUpBeforeClass(): void
    {
        self::$cacheDir = __DIR__ . '/../cache/' . (new \ReflectionClass(__CLASS__))->getShortName();
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
            ->forMember('fixed', Operation::setTo(100))
            ;

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
}