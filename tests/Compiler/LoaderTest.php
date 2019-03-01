<?php
declare(strict_types=1);

namespace Seacommerce\Mapper\Test\Compiler;

use Seacommerce\Mapper\Compiler\CachedLoader;
use Seacommerce\Mapper\Compiler\EvalLoader;
use Seacommerce\Mapper\Compiler\NativeCompiler;
use Seacommerce\Mapper\Configuration;
use Seacommerce\Mapper\Operation;
use Symfony\Component\Filesystem\Filesystem;

class LoaderTest extends \PHPUnit\Framework\TestCase
{
    private static $cacheDir;

    /**
     * @throws \ReflectionException
     */
    public static function setUpBeforeClass(): void
    {
        self::$cacheDir = __DIR__ . '/../../cache/' . (new \ReflectionClass(__CLASS__))->getShortName();
        (new Filesystem())->remove(self::$cacheDir);
    }

    public function testConfigurationHashShouldBeDeterministic()
    {
        $configuration = (new Configuration(\Seacommerce\Mapper\Test\Model\PublicFields\Source::class, \Seacommerce\Mapper\Test\Model\PublicFields\Target::class, __FUNCTION__))
            ->autoMap()
            ->forMember('ignore', Operation::ignore())
            ->forMember('dateTime', Operation::fromProperty('date'))
            ->forMember('callback', Operation::mapFrom(function () {
                return 'x';
            }))
            ->forMember('fixed', Operation::setTo(100));
        $hash1 = $configuration->getHash();
        $hash2 = $configuration->getHash();
        $this->assertEquals($hash1, $hash2);
    }

    public function testLoadWithoutCache()
    {
        $configuration = (new Configuration(\Seacommerce\Mapper\Test\Model\PublicFields\Source::class, \Seacommerce\Mapper\Test\Model\PublicFields\Target::class, __FUNCTION__))
            ->autoMap()
            ->forMember('ignore', Operation::ignore())
            ->forMember('dateTime', Operation::fromProperty('date'))
            ->forMember('callback', Operation::mapFrom(function () {
                return 'x';
            }))
            ->forMember('fixed', Operation::setTo(100));
        $loader = new EvalLoader(new NativeCompiler());
        $loader->load($configuration);
        $loader->load($configuration);
        $this->assertTrue(class_exists($configuration->getMapperFullClassName()));
    }

    public function testLoadWithCache()
    {
        $configuration = (new Configuration(\Seacommerce\Mapper\Test\Model\PublicFields\Source::class, \Seacommerce\Mapper\Test\Model\PublicFields\Target::class, __FUNCTION__))
            ->autoMap()
            ->forMember('ignore', Operation::ignore())
            ->forMember('dateTime', Operation::fromProperty('date'))
//            ->forMember('dateTime', Operation::ignore())
            ->forMember('callback', Operation::mapFrom(function () {
                return 'x';
            }))
            ->forMember('fixed', Operation::setTo(100));
        $compiler = new NativeCompiler();
        $loader = new CachedLoader($compiler, self::$cacheDir);
        $loader->warmup($configuration);
        $loader->load($configuration);
        $this->assertTrue(class_exists($configuration->getMapperFullClassName()));
    }
}