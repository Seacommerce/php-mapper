<?php


namespace Seacommerce\Mapper\Compiler;


use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Name;
use Seacommerce\Mapper\Property;
use Seacommerce\Mapper\PropertyReadAccessor;
use Seacommerce\Mapper\PropertyWriteAccessor;

class PropertyAccess
{
    /**
     * @param  Expr $var
     * @param Property $property
     * @return Expr
     */
    public static function getReadExpr(Expr $var, Property $property): Expr
    {
        if ($property->getReadAccessor()->getType() === PropertyReadAccessor::ACCESS_TYPE_PROPERTY) {
            return new PropertyFetch($var, $property->getReadAccessor()->getName());
        } else if ($property->getReadAccessor()->getType() === PropertyReadAccessor::ACCESS_TYPE_METHOD) {
            return new MethodCall($var, $property->getReadAccessor()->getName(), []);
        }
        throw new \InvalidArgumentException("Invalid value for \$property->getReadAccessor()->getType()");
    }

    /**
     * @param Expr $var
     * @param Property $property
     * @param Expr|Name $val
     * @return Expr
     */
    public static function getWriteExpr(Expr $var, Property $property, $val): Expr
    {
        if ($property->getWriteAccessor()->getType() === PropertyWriteAccessor::ACCESS_TYPE_PROPERTY) {
            return new Expr\Assign(new PropertyFetch($var, $property->getWriteAccessor()->getName()), $val);
        } else if ($property->getWriteAccessor()->getType() === PropertyWriteAccessor::ACCESS_TYPE_METHOD) {
            return new MethodCall($var, $property->getWriteAccessor()->getName(), [$val]);
        }
        throw new \InvalidArgumentException("Invalid value for \$property->getReadAccessor()->getType()");
    }
}