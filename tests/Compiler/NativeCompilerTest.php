<?php


namespace Seacommerce\Mapper\Test\Compiler;


use PHPUnit\Framework\TestCase;
use Seacommerce\Mapper\Compiler\NativeCompiler;
use Seacommerce\Mapper\Configuration;
use Seacommerce\Mapper\Exception\PropertyNotFoundException;
use Seacommerce\Mapper\Operation;
use Seacommerce\Mapper\Test\Model;
use Seacommerce\Mapper\ValueConverter\DateTimeImmutableConverter;

class NativeCompilerTest extends TestCase
{
    /**
     * @throws PropertyNotFoundException
     */
    public function testNoCacheShouldEval()
    {
        $compiler = new NativeCompiler();
        $configuration = new Configuration(Model\GettersSetters\Source::class, Model\GettersSetters\Target::class, 'NativeCompiler_testNoCacheShouldEval');
        $configuration
            ->autoMap()
            ->allowMapFromSubClass()
            ->forMember('dateMutable', Operation::fromProperty('dateImmutable')->useConverter(DateTimeImmutableConverter::toMutable()))
            ->forMember('ignore', Operation::ignore())
            ->forMember('dateTime', Operation::fromProperty('date'))
            ->forMember('callback', Operation::mapFrom(function () {
                return 'x';
            }))
            ->forMember('fixed', Operation::setTo(100))
            ->validate();
        $compiler->compile($configuration);
        $this->assertTrue(class_exists($configuration->getMapperFullClassName()));
    }

    /**
     * @throws PropertyNotFoundException
     */
    public function testCacheShouldInclude()
    {
        $compiler = new NativeCompiler('./var/cache');
        $configuration = new Configuration(Model\GettersSetters\Source::class, Model\GettersSetters\Target::class, 'NativeCompiler_testCacheShouldInclude');
        $configuration
            ->autoMap()
            ->allowMapFromSubClass()
            ->forMember('dateMutable', Operation::fromProperty('dateImmutable')->useConverter(DateTimeImmutableConverter::toMutable()))
            ->forMember('ignore', Operation::ignore())
            ->forMember('dateTime', Operation::fromProperty('date'))
            ->forMember('callback', Operation::mapFrom(function () {
                return 'x';
            }))
            ->forMember('fixed', Operation::setTo(100))
            ->validate();
        $compiler->compile($configuration);
        $this->assertTrue(class_exists($configuration->getMapperFullClassName()));
    }
}