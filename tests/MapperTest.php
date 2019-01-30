<?php
declare(strict_types=1);

namespace Seacommerce\Mapper\Test;

use DateTime;
use Seacommerce\Mapper\Compiler\PropertyAccessCompiler;
use Seacommerce\Mapper\Mapper;
use Seacommerce\Mapper\Registry;

class MapperTest extends \PHPUnit\Framework\TestCase
{
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

        $mapper = new Mapper($registry, new PropertyAccessCompiler( './var/cache'));


        $source = new Model\PublicFields\Source();
        $source->id = 1;
        $source->name= "Sil";
        $source->date= new DateTime();

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