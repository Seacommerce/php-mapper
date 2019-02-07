<?php
declare(strict_types=1);

namespace Seacommerce\Mapper\Test;

use DateTime;
use Seacommerce\Mapper\Compiler\PropertyAccessCompiler;
use Seacommerce\Mapper\Exception\ClassNotFoundException;
use Seacommerce\Mapper\Exception\ConfigurationNotFoundException;
use Seacommerce\Mapper\Exception\InvalidArgumentException;
use Seacommerce\Mapper\Mapper;
use Seacommerce\Mapper\Registry;

class MapperTest extends \PHPUnit\Framework\TestCase
{
    public function testMissingConfigurationThrowsException()
    {
        $this->expectException(ConfigurationNotFoundException::class);
        $registry = new Registry();
        $mapper = new Mapper($registry, new PropertyAccessCompiler('./var/cache'));
        $mapper->map(new Model\PublicFields\Source(), Model\PublicFields\Target::class);
    }

    public function testNonObjectOrArrayAsSourceThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        $registry = new Registry();
        $mapper = new Mapper($registry, new PropertyAccessCompiler('./var/cache'));
        $mapper->map(1, Model\PublicFields\Target::class);
    }

    public function testNonExistingClassNameAsTargetThrowsException()
    {
        $this->expectException(ClassNotFoundException::class);
        $registry = new Registry();
        $mapper = new Mapper($registry, new PropertyAccessCompiler('./var/cache'));
        $mapper->map(new Model\PublicFields\Source(), 'NonExistingClass');
    }

    public function testCompile()
    {
        $registry = new Registry();
        $registry->add(Model\PublicFields\Source::class, Model\PublicFields\Target::class)
            ->automap()
            ->ignore('ignore', 'dateTime',  'callback', 'fixed');

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
     */
    public function testMapToClass()
    {
        $registry = new Registry();
        $registry->add(Model\PublicFields\Source::class, Model\PublicFields\Target::class)
            ->automap()
            ->ignore('ignore')
            ->map(['dateTime' => 'date'])
            ->callback('callback', function () {
                return 'x';
            })
            ->constValue('fixed', 100);

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
}