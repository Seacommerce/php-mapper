<?php

namespace Seacommerce\Mapper\Compiler;

use PhpParser\BuilderFactory;
use PhpParser\Comment\Doc;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node;
use Seacommerce\Mapper\AbstractMapper;
use Seacommerce\Mapper\ConfigurationInterface;
use Seacommerce\Mapper\Context;
use Seacommerce\Mapper\MapperInterface;
use Seacommerce\Mapper\MapFrom;
use Seacommerce\Mapper\FromProperty;
use Seacommerce\Mapper\Ignore;
use Seacommerce\Mapper\OperationInterface;
use Seacommerce\Mapper\Property;
use Seacommerce\Mapper\RegistryInterface;
use Seacommerce\Mapper\SetTo;
use Seacommerce\Mapper\ValueConverter\ValueConverterInterface;
use Symfony\Component\PropertyInfo\Type;

class NativeCompiler implements CompilerInterface
{
    /**
     * @param ConfigurationInterface $configuration
     * @return Node
     * @throws \Seacommerce\Mapper\Exception\PropertyNotFoundException
     * @throws \Seacommerce\Mapper\Exception\ValidationErrorsException
     */
    public function compile(ConfigurationInterface $configuration): Node
    {
        static $factory = null;
        if ($factory == null) $factory = new BuilderFactory();

        $prepared = $configuration->prepare();
        $prepared->validate();

        $thisMapper = new Variable('this');
        $source = new Variable('source');
        $target = new Variable('target');
        $mapper = new Variable('mapper');
        $context = new Variable('context');

        $getOperation = function (string $property) use ($factory, $thisMapper) {
            return $factory->methodCall($thisMapper, 'getOperation', [$factory->val($property)]);
        };
        $getValueConverter = function (string $fromType, string $toType, $converter, Node\Expr $read) use ($factory, $thisMapper) {
            $c = $factory->methodCall($thisMapper, 'getValueConverter', [$factory->val($fromType), $factory->val($toType)]);
            if (is_callable($converter)) {
                $read = $factory->funcCall($c, [$read]);
            } else if ($converter instanceof ValueConverterInterface) {
                $read = $factory->methodCall($c, 'convert', [$read]);
            }
            return $read;
        };

        $sourceProperties = $prepared->getSourceProperties();
        $targetProperties = $prepared->getTargetProperties();

        if ($configuration->getBefore()) {
            $call = $factory->funcCall($factory->methodCall($factory->methodCall($context, 'getConfiguration'), 'getBefore'), [
                $source, $target, $mapper, $context
            ]);
            $assign = new Assign($target, new  Coalesce($call, $target));
            $stmts[] = $assign;
        }

        $stmts[] = new If_(new Identical(new ConstFetch(new Name('null')), $source), ['stmts' => [new Return_($source)]]);
        /**
         * @var string $property
         * @var OperationInterface $operation
         */
        foreach ($prepared->getOperations() as $property => $operation) {
            $targetProperty = $targetProperties[$property];
            if ($operation instanceof MapFrom) {
                $read = $factory->funcCall($factory->methodCall($getOperation($property), 'getCallback'), [
                    $factory->val($property), $source, $target, $mapper, $context
                ]);
                $write = PropertyAccess::getWriteExpr($target, $targetProperty, $read);
                $stmts[] = $write;
            } else if ($operation instanceof FromProperty) {
                $sourceProperty = $sourceProperties[$operation->getFrom()];
                $read = PropertyAccess::getReadExpr($source, $sourceProperty);

                if ($operation->getConverter() !== null) {
                    if (is_callable($operation->getConverter())) {
                        $read = $factory->funcCall($factory->methodCall($getOperation($property), 'getConverter'), [$read]);
                    } else if ($operation->getConverter() instanceof ValueConverterInterface) {
                        $read = $factory->methodCall($factory->methodCall($getOperation($property), 'getConverter'), 'convert', [$read]);
                    }
                } else if ($configuration->getRegistry() !== null) {
                    list($formType, $toType, $converter) = $this->getValueConverter($configuration->getRegistry(), $sourceProperty, $targetProperty);
                    if ($converter !== null) {
                        $read = $getValueConverter($formType, $toType, $converter, $read);
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
        if ($configuration->getAfter()) {
            $call = $factory->funcCall($factory->methodCall($factory->methodCall($context, 'getConfiguration'), 'getAfter'), [
                $source, $target, $mapper, $context
            ]);
            $assign = new Assign($target, new  Coalesce($call, $target));
            $stmts[] = $assign;
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
//            $sourceType = $configuration->getSourceClass();
            $sourceType = 'S';
            $builder->addStmt($factory->use($configuration->getSourceClass())->as($sourceType));

        }
        if ($configuration->getTargetClass() === 'array') {
            $targetType = 'array';
        } else {
//            $targetType = $configuration->getTargetClass();
            $targetType = 'T';
            $builder->addStmt($factory->use($configuration->getTargetClass())->as($targetType));
        }
        $builder
            ->addStmt($factory->class($className)
                ->makeFinal()
                ->extend(new Name\FullyQualified(AbstractMapper::class))
                ->addStmt($factory->method('map')
                    ->makePublic()
                    ->makeFinal()
                    ->addParam($factory->param($source->name)->setType(new Node\NullableType($sourceType)))
                    ->addParam($factory->param($target->name)->setType($targetType))
                    ->addParam($factory->param($mapper->name)->setType('MapperInterface'))
                    ->addParam($factory->param($context->name)->setType('Context'))
                    ->addStmts($stmts)
                )
            );

        $node = $builder->getNode();
        return $node;
    }


    private function getValueConverter(RegistryInterface $registry, Property $sourceProperty, Property $targetProperty)
    {
        /** @var Type[] $fromTypes */
        $fromTypes = $sourceProperty->getTypes();
        /** @var Type[] $toTypes */
        $toTypes = $targetProperty->getTypes();
        if ($fromTypes !== null && $fromTypes !== null && count($fromTypes) !== 1 && count($toTypes) !== 1) {
            return null;
        }

        $fromType = array_shift($fromTypes);
        $toType = array_shift($toTypes);

        $f = $fromType->getClassName() ?? $fromType->getBuiltinType();
        $t = $toType->getClassName() ?? $toType->getBuiltinType();
        if ($f === null || $t === null) {
            return null;
        }

        $converter = $registry->getValueConverter($f, $t);
        return [$f, $t, $converter];
    }
}