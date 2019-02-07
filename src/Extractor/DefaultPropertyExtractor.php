<?php

namespace Seacommerce\Mapper\Extractor;

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
        $properties = $this->propertyInfoExtractor->getProperties($class);
        return $properties;
    }
}