<?php


namespace Seacommerce\Mapper\Test;


use PHPUnit\Framework\TestCase;
use Seacommerce\Mapper\Compiler\PropertyAccessCompiler;
use Seacommerce\Mapper\Configuration;

class PropertyAccessCompilerTest extends TestCase
{
    public function testNoCacheShouldEval()
    {
        $compiler = new PropertyAccessCompiler();

        $configuration = new Configuration(Model\GettersSetters\Source::class, Model\GettersSetters\Target::class, 'PropertyAccessCompiler');
        $fullClassName = $compiler->getMappingFullClassName($configuration);
        $compiler->compile($configuration);
        $this->assertTrue(class_exists($fullClassName));
    }

    public function testCacheShouldInclude()
    {
        $compiler = new PropertyAccessCompiler('./var/cache');
        $configuration = new Configuration(Model\GettersSetters\Source::class, Model\GettersSetters\Target::class, 'testCacheShouldInclude');
        $fullClassName = $compiler->getMappingFullClassName($configuration);
        $compiler->compile($configuration);
        $this->assertTrue(class_exists($fullClassName));
    }
}