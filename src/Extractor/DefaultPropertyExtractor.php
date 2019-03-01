<?php

namespace Seacommerce\Mapper\Extractor;

use Seacommerce\Mapper\Property;
use Seacommerce\Mapper\PropertyReadAccessor;
use Seacommerce\Mapper\PropertyWriteAccessor;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;

class DefaultPropertyExtractor implements PropertyExtractorInterface
{
    /** @var PropertyInfoExtractor */
    private $propertyInfoExtractor;

    public function __construct()
    {
        $phpDocExtractor = new PhpDocExtractor();
        $reflectionExtractor = new ReflectionExtractor();

        $listExtractors = [$reflectionExtractor];
        $typeExtractors = [$phpDocExtractor, $reflectionExtractor];
        $descriptionExtractors = [$phpDocExtractor];
        $accessExtractors = [$reflectionExtractor];
        $propertyInitializableExtractors = [$reflectionExtractor];
        $this->propertyInfoExtractor = new PropertyInfoExtractor($listExtractors, $typeExtractors, $descriptionExtractors, $accessExtractors, $propertyInitializableExtractors);
    }

    public function getProperties(string $class): ?array
    {
        try {
            $reflectionClass = new \ReflectionClass($class);
        } catch (\ReflectionException $e) {
            return null;
        }

        $reflectionProperties = $reflectionClass->getProperties();

        $properties = array();
        foreach ($reflectionProperties as $reflectionProperty) {
            if ($reflectionProperty->isPublic()) {
                $name = $reflectionProperty->name;
                $types = $this->propertyInfoExtractor->getTypes($class, $name);
                $properties[$reflectionProperty->name] = new Property($reflectionProperty->name, $types,
                    new PropertyReadAccessor($name, PropertyReadAccessor::ACCESS_TYPE_PROPERTY),
                    new PropertyWriteAccessor($name, PropertyWriteAccessor::ACCESS_TYPE_PROPERTY));
            }
        }

        foreach ($reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $reflectionMethod) {
            if ($reflectionMethod->isStatic()) {
                continue;
            }

            $propertyName = $this->getPropertyName($reflectionMethod->name);
            if (!$propertyName || isset($properties[$propertyName])) {
                continue;
            }
            if (!$reflectionClass->hasProperty($propertyName) && !preg_match('/^[A-Z]{2,}/', $propertyName)) {
                $propertyName = lcfirst($propertyName);
            }

            /**
             * @var \ReflectionMethod $readAccessorMethod
             * @var \ReflectionMethod $writeAccessorMethod
             */
            list($readAccessorMethod) = $this->getAccessorMethod($class, $propertyName);
            list($writeAccessorMethod) = $this->getMutatorMethod($class, $propertyName);
            $types = $this->propertyInfoExtractor->getTypes($class, $propertyName);
            $properties[$propertyName] = new Property($propertyName, $types,
                $readAccessorMethod === null ? null : new PropertyReadAccessor($readAccessorMethod->getName(), PropertyReadAccessor::ACCESS_TYPE_METHOD),
                $writeAccessorMethod === null ? null : new PropertyWriteAccessor($writeAccessorMethod->getName(), PropertyWriteAccessor::ACCESS_TYPE_METHOD));
        }
        return $properties;
    }


    /**
     * Gets the accessor method.
     *
     * Returns an array with a the instance of \ReflectionMethod as first key
     * and the prefix of the method as second or null if not found.
     * @param string $class
     * @param string $property
     * @return array|null
     */
    private function getAccessorMethod(string $class, string $property): ?array
    {
        $ucProperty = ucfirst($property);

        foreach (self::$accessorPrefixes as $prefix) {
            try {
                $reflectionMethod = new \ReflectionMethod($class, $prefix . $ucProperty);
                if ($reflectionMethod->isStatic()) {
                    continue;
                }

                if (0 === $reflectionMethod->getNumberOfRequiredParameters()) {
                    return array($reflectionMethod, $prefix);
                }
            } catch (\ReflectionException $e) {
                // Return null if the property doesn't exist
            }
        }

        return null;
    }

    /**
     * Returns an array with a the instance of \ReflectionMethod as first key
     * and the prefix of the method as second or null if not found.
     * @param string $class
     * @param string $property
     * @return array|null
     */
    private function getMutatorMethod(string $class, string $property): ?array
    {
        $ucProperty = ucfirst($property);

        foreach (self::$mutatorPrefixes as $prefix) {
            $names = array($ucProperty);

            foreach ($names as $name) {
                try {
                    $reflectionMethod = new \ReflectionMethod($class, $prefix . $name);
                    if ($reflectionMethod->isStatic()) {
                        continue;
                    }

                    // Parameter can be optional to allow things like: method(array $foo = null)
                    if ($reflectionMethod->getNumberOfParameters() >= 1) {
                        return array($reflectionMethod, $prefix);
                    }
                } catch (\ReflectionException $e) {
                    // Try the next prefix if the method doesn't exist
                }
            }
        }

        return null;
    }

    /**
     * Copied from Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor
     *
     * @param string $methodName
     * @return string|null
     */
    private function getPropertyName(string $methodName): ?string
    {
        $pattern = implode('|', array_merge(self::$accessorPrefixes, self::$mutatorPrefixes));

        if ('' !== $pattern && preg_match('/^(' . $pattern . ')(.+)$/i', $methodName, $matches)) {
            return $matches[2];
        }
        return null;
    }

    /**
     * @internal
     */
    public static $mutatorPrefixes = array('set');

    /**
     * @internal
     */
    public static $accessorPrefixes = array('is', 'can', 'get', 'has');
}