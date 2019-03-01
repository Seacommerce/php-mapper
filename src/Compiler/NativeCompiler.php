<?php

namespace Seacommerce\Mapper\Compiler;

use PhpParser\BuilderFactory;
use PhpParser\Comment\Doc;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\Return_;
use PhpParser\PrettyPrinter;
use Seacommerce\Mapper\ConfigurationInterface;
use Seacommerce\Mapper\Context;
use Seacommerce\Mapper\MapperInterface;
use Seacommerce\Mapper\MapFrom;
use Seacommerce\Mapper\FromProperty;
use Seacommerce\Mapper\Ignore;
use Seacommerce\Mapper\SetTo;
use Seacommerce\Mapper\ValueConverter\ValueConverterInterface;

class NativeCompiler implements CompilerInterface
{
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

        $source = new Variable('source');
        $target = new Variable('target');
        $mapper = new Variable('mapper');
        $context = new Variable('context');

        $getOperation = function (string $property) use ($factory, $context) {
            return $factory->methodCall($factory->methodCall($context, 'getConfiguration'), 'getOperation', [$factory->val($property)]);
        };


        $sourceProperties = $configuration->getSourceProperties();
        $targetProperties = $configuration->getTargetProperties();

        $stmts[] = new If_(new Identical(new ConstFetch(new Name('null')), $source), ['stmts' => [new Return_($source)]]);
        foreach ($configuration->getOperations() as $property => $operation) {
            if ($operation instanceof MapFrom) {
                $read = $factory->funcCall($factory->methodCall($getOperation($property), 'getCallback'), [
                    $factory->val($property), $source, $target, $mapper, $context
                ]);
                $write = PropertyAccess::getWriteExpr($target, $targetProperties[$property], $read);
                $stmts[] = $write;
            } else if ($operation instanceof FromProperty) {
                $read = PropertyAccess::getReadExpr($source, $sourceProperties[$operation->getFrom()]);
                if ($operation->getConverter() !== null) {
                    if (is_callable($operation->getConverter())) {
                        $read = $factory->funcCall($factory->methodCall($getOperation($property), 'getConverter'), [$read]);
                    } else if ($operation->getConverter() instanceof ValueConverterInterface) {
                        $read = $factory->methodCall($factory->methodCall($getOperation($property), 'getConverter'), 'convert', [$read]);
                    }
                }
                $write = PropertyAccess::getWriteExpr($target, $targetProperties[$property], $read);
                $stmts[] = $write;
            } else if ($operation instanceof Ignore) {
                $nop = new Nop();
                $nop->setDocComment(new Doc("// Property '{$property}' was explicitly ignored."));
                $stmts[] = $nop;
            } else if ($operation instanceof SetTo) {
                $read = $factory->val($operation->getValue());
                $write = PropertyAccess::getWriteExpr($target, $targetProperties[$property], $read);
                $stmts[] = $write;
            }
        }
        $stmts[] = new Return_($target);

        $className = $configuration->getMapperClassName();
        $builder = $factory
            ->namespace($configuration->getMapperNamespace())
            ->addStmt($factory->use(Context::class))
            ->addStmt($factory->use(MapperInterface::class));

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
}