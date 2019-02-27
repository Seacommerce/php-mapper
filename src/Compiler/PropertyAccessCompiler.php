<?php

namespace Seacommerce\Mapper\Compiler;

use PhpParser\BuilderFactory;
use PhpParser\Comment\Doc;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Static_;
use PhpParser\PrettyPrinter;
use Seacommerce\Mapper\ConfigurationInterface;
use Seacommerce\Mapper\Context;
use Seacommerce\Mapper\MapperInterface;
use Seacommerce\Mapper\MapFrom;
use Seacommerce\Mapper\FromProperty;
use Seacommerce\Mapper\Ignore;
use Seacommerce\Mapper\SetTo;
use Seacommerce\Mapper\ValueConverter\ValueConverterInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

class PropertyAccessCompiler implements CompilerInterface
{
    /** @var string|null */
    private $namespace = 'Mappings';

    /** @var string|null */
    private $cacheFolder;

    /**
     * Compiler constructor.
     * @param string $cacheFolder
     */
    public function __construct(?string $cacheFolder = null)
    {
        $this->cacheFolder = $cacheFolder;
    }

    /**
     * @return string|null
     */
    public function getNamespace(): ?string
    {
        return $this->namespace;
    }

    /**
     * @return string|null
     */
    public function getCacheFolder(): ?string
    {
        return $this->cacheFolder;
    }

    public function compile(ConfigurationInterface $configuration): void
    {
        static $factory = null;
        static $prettyPrinter = null;
        if ($factory == null) $factory = new BuilderFactory();
        if ($prettyPrinter == null) $prettyPrinter = new PrettyPrinter\Standard();

        $accessorBuilder = new Variable('accessorBuilder');
        $accessor = new Variable('accessor');
        $source = new Variable('source');
        $target = new Variable('target');
        $mapper = new Variable('mapper');
        $context = new Variable('context');

        $getOperation = function (string $property) use ($factory, $context) {
            return new ArrayDimFetch($factory->methodCall($factory->methodCall($context, 'getConfiguration'), 'getOperations'), $factory->val($property));
        };

        $stmts[] = new If_(new Identical(new ConstFetch(new Name('null')), $source), ['stmts' => [new Return_($source)]]);
        $stmts[] = new Static_([$accessor]);
        $stmts[] = new If_(new Identical(new ConstFetch(new Name('null')), $accessor), [
            'stmts' => [
                new Expression(new Assign($accessorBuilder, $factory->methodCall($factory->staticCall('PropertyAccess', 'createPropertyAccessorBuilder'), 'enableExceptionOnInvalidIndex'))),
                new Expression(new Assign($accessor, $factory->methodCall($accessorBuilder, 'getPropertyAccessor')))
            ]
        ]);
        foreach ($configuration->getOperations() as $property => $operation) {
            if ($operation instanceof MapFrom) {
                $stmts[] = $factory->methodCall($accessor, 'setValue', [
                    $target,
                    $property,
                    $factory->funcCall($factory->methodCall($getOperation($property), 'getCallback'), [
                        $factory->val($property), $source, $target, $mapper, $context
                    ])
                ]);
            } else if ($operation instanceof FromProperty) {
                $get = $factory->methodCall($accessor, 'getValue', [$source, $operation->getFrom()]);
                if ($operation->getConverter() !== null) {
                    if (is_callable($operation->getConverter())) {
                        $get = $factory->funcCall($factory->methodCall($getOperation($property), 'getConverter'), [$get]);
                    } else if ($operation->getConverter() instanceof ValueConverterInterface) {
                        $get = $factory->methodCall($factory->methodCall($getOperation($property), 'getConverter'), 'convert', [$get]);
                    }
                }
                $set = $factory->methodCall($accessor, 'setValue', [$target, $property, $get]);
                $stmts[] = $set;

            } else if ($operation instanceof Ignore) {
                $nop = new Nop();
                $nop->setDocComment(new Doc("// Property '{$property}' was explicitly ignored."));
                $stmts[] = $nop;
            } else if ($operation instanceof SetTo) {
                $stmts[] = $factory->methodCall($accessor, 'setValue', [
                    $target,
                    $property,
                    $factory->val($operation->getValue())
                ]);
            }
        }
        $stmts[] = new Return_($target);

        $className = $this->getMappingClassName($configuration);
        $builder = $factory
            ->namespace($this->namespace)
            ->addStmt($factory->use(Context::class))
            ->addStmt($factory->use(MapperInterface::class))
            ->addStmt($factory->use(PropertyAccess::class));

        if ($configuration->getSourceClass() === 'array') {
            $sourceType = 'array';
        } else {
            $sourceType = 'S';
            $builder->addStmt($factory->use($configuration->getSourceClass())->as($sourceType));

        }
        if ($configuration->getTargetClass() === 'array') {
            $targetType = 'array';
        } else {
            $targetType = 'T';
            $builder->addStmt($factory->use($configuration->getTargetClass())->as($targetType));
        }
        $builder
            ->addStmt($factory->class($className)
                ->makeFinal()
                ->addStmt($factory->method('map')
                    ->makePublic()
                    ->makeFinal()
                    ->addParam($factory->param($source->name)->setType("?{$sourceType}"))
                    ->addParam($factory->param($target->name)->setType($targetType))
                    ->addParam($factory->param($mapper->name)->setType('MapperInterface'))
                    ->addParam($factory->param($context->name)->setType('Context'))
                    ->addStmts($stmts)
                )
            );

        $node = $builder->getNode();

        if (!empty($this->cacheFolder)) {
            $str = $prettyPrinter->prettyPrintFile([$node]) . PHP_EOL;
            $filePath = $this->cacheFolder . '/' . $className . '.php';
            if (!empty($this->cacheFolder)) {
                if (!file_exists($this->cacheFolder)) {
                    mkdir($this->cacheFolder, 0777, true);
                }
                file_put_contents($filePath, $str);
            }
            require_once $filePath;
        } else {
            $str = $prettyPrinter->prettyPrint([$node]) . PHP_EOL;
            eval($str);
        }
    }

    public function getMappingClassName(ConfigurationInterface $configuration): string
    {
        $sourceClass = preg_replace('/\\\\{1}/', '_', $configuration->getSourceClass());
        $destClass = preg_replace('/\\\\{1}/', '_', $configuration->getTargetClass());
        $name = "__{$configuration->getScope()}_{$sourceClass}_to_{$destClass}";
        return $name;
    }

    public function getMappingFullClassName(ConfigurationInterface $configuration): string
    {
        $short = $this->getMappingClassName($configuration);
        return "{$this->namespace}\\{$short}";
    }
}