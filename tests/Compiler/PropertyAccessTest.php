<?php

namespace Seacommerce\Mapper\Test\Compiler;

use PhpParser\BuilderFactory;
use PhpParser\Node\Expr\Variable;
use PhpParser\PrettyPrinter\Standard;
use PHPUnit\Framework\TestCase;
use Seacommerce\Mapper\Compiler\PropertyAccess;
use Seacommerce\Mapper\Extractor\DefaultPropertyExtractor;
use Seacommerce\Mapper\Test\Model\GettersSetters\SourceSubclass;

class PropertyAccessTest extends TestCase
{
    private static $factory;
    /** @var Standard */
    private static $prettyPrinter;

    public static function setUpBeforeClass() : void
    {
        self::$factory = new BuilderFactory();
        self::$prettyPrinter = new Standard();
    }

    public function testGetWriteExpr()
    {
        $extractor = new DefaultPropertyExtractor();
        $properties = $extractor->getProperties(SourceSubclass::class);
        $name = new Variable('aap');
        foreach ($properties as $property) {
            $expr = PropertyAccess::getWriteExpr($name, $property, PropertyAccess::getReadExpr($name, $property));
            $this->assertNotNull($expr);
        }
    }
}