<?php


namespace Seacommerce\Mapper\Test;


use DateTime;
use Seacommerce\Mapper\Compiler\NativeCompiler;
use Seacommerce\Mapper\Mapper;
use Seacommerce\Mapper\Operation;
use Seacommerce\Mapper\Registry;
use Seacommerce\Mapper\ValueConverter\DateTimeImmutableConverter;

class PerformanceTest extends \PHPUnit\Framework\TestCase
{
    private const ITERATIONS = 1000;
    /**
     * @throws \Exception
     */
    public function testNative(): void
    {
        $source = new Model\GettersSetters\SourceSubclass();
        $source->setId(1);
        $source->setName("Sil");
        $source->setDate(new DateTime());
        $source->setDateImmutable(new \DateTimeImmutable());
        $startMapping = microtime(true);
        for ($i = 0; $i < self::ITERATIONS; $i++) {
            if (null === $source) {
                continue;
            }
            $target = new Model\GettersSetters\Target();
            $target->setId($source->getId());
            $target->setName($source->getName());
            $target->setDateTime($source->getDate());
            $target->setCallback((function () {
                return 'x';
            })());
            $target->setFixed(100);
        }
        $endMapping = microtime(true);
        echo "Time native: " . ($endMapping - $startMapping) . PHP_EOL;
        $this->assertTrue(true);
    }


    /**
     * @throws \Exception
     */
    public function testMapper(): void
    {
        $startInit = microtime(true);
        $registry = new Registry('PerformanceTest_testMapper');
        $registry->add(Model\GettersSetters\Source::class, Model\GettersSetters\Target::class)
            ->autoMap()
            ->allowMapFromSubClass()
            ->forMember('dateMutable', Operation::ignore())
            ->forMember('ignore', Operation::ignore())
            ->forMember('dateTime', Operation::fromProperty('date'))
            ->forMember('callback', Operation::mapFrom(function () {
                return 'x';
            }))
            ->forMember('fixed', Operation::setTo(100))
            ->validate();


        $mapper = new Mapper($registry, new NativeCompiler('./var/cache'));
        $mapper->compile();

        $source = new Model\GettersSetters\Source();
        $source->setId(1);
        $source->setName("Sil");
        $source->setDate(new DateTime());
        $source->setDateImmutable(new \DateTimeImmutable());
        $endInit = microtime(true);
        $startMapping = microtime(true);
        for ($i = 0; $i < self::ITERATIONS; $i++) {
            $target = new Model\GettersSetters\Target();
            /** @var Model\GettersSetters\Target $target */
            $target = $mapper->map($source, $target);
        }
        $endMapping = microtime(true);
        echo "Time mapper: " . ($endMapping - $startMapping) . ' (init: ' . ($endInit - $startInit) . ')' . PHP_EOL;
        $this->assertTrue(true);
    }
}