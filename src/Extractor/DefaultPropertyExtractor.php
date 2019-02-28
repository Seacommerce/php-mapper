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

//    public function getProperties(string $class): ?array
//    {
//        $names = $this->propertyInfoExtractor->getProperties($class);
//        if($names == null) {
//            return null;
//        }
//        $properties = [];
//        foreach ($names as $name) {
//            $properties[$name] = [
//                'types' => $this->propertyInfoExtractor->getTypes($class, $name)
//            ];
//        }
//        return $properties;
//    }


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

            $propertyName = $this->getPropertyName($reflectionMethod->name, $reflectionProperties);
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
                new PropertyReadAccessor($readAccessorMethod->getName(), PropertyReadAccessor::ACCESS_TYPE_METHOD),
                new PropertyWriteAccessor($writeAccessorMethod->getName(), PropertyWriteAccessor::ACCESS_TYPE_METHOD));
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
        $ucSingulars = (array)self::singularize($ucProperty);

        foreach (self::$mutatorPrefixes as $prefix) {
            $names = array($ucProperty);
            if (\in_array($prefix, self::$arrayMutatorPrefixes)) {
                $names = array_merge($names, $ucSingulars);
            }

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
     * @param array $reflectionProperties
     * @return string|null
     */
    private function getPropertyName(string $methodName, array $reflectionProperties): ?string
    {
        $pattern = implode('|', array_merge(self::$accessorPrefixes, self::$mutatorPrefixes));

        if ('' !== $pattern && preg_match('/^(' . $pattern . ')(.+)$/i', $methodName, $matches)) {
            if (!\in_array($matches[1], self::$arrayMutatorPrefixes)) {
                return $matches[2];
            }

            foreach ($reflectionProperties as $reflectionProperty) {
                foreach ((array)$this->singularize($reflectionProperty->name) as $name) {
                    if (strtolower($name) === strtolower($matches[2])) {
                        return $reflectionProperty->name;
                    }
                }
            }
            return $matches[2];
        }
        return null;
    }

    /**
     * Copied from Symfony\Component\Inflector\Inflector
     *
     * @param string $plural
     * @return array|string
     */
    public static function singularize(string $plural)
    {
        $pluralRev = strrev($plural);
        $lowerPluralRev = strtolower($pluralRev);
        $pluralLength = \strlen($lowerPluralRev);

        // The outer loop iterates over the entries of the plural table
        // The inner loop $j iterates over the characters of the plural suffix
        // in the plural table to compare them with the characters of the actual
        // given plural suffix
        foreach (self::$pluralMap as $map) {
            $suffix = $map[0];
            $suffixLength = $map[1];
            $j = 0;

            // Compare characters in the plural table and of the suffix of the
            // given plural one by one
            while ($suffix[$j] === $lowerPluralRev[$j]) {
                // Let $j point to the next character
                ++$j;

                // Successfully compared the last character
                // Add an entry with the singular suffix to the singular array
                if ($j === $suffixLength) {
                    // Is there any character preceding the suffix in the plural string?
                    if ($j < $pluralLength) {
                        $nextIsVocal = false !== strpos('aeiou', $lowerPluralRev[$j]);

                        if (!$map[2] && $nextIsVocal) {
                            // suffix may not succeed a vocal but next char is one
                            break;
                        }

                        if (!$map[3] && !$nextIsVocal) {
                            // suffix may not succeed a consonant but next char is one
                            break;
                        }
                    }

                    $newBase = substr($plural, 0, $pluralLength - $suffixLength);
                    $newSuffix = $map[4];

                    // Check whether the first character in the plural suffix
                    // is uppercased. If yes, uppercase the first character in
                    // the singular suffix too
                    $firstUpper = ctype_upper($pluralRev[$j - 1]);

                    if (\is_array($newSuffix)) {
                        $singulars = array();

                        foreach ($newSuffix as $newSuffixEntry) {
                            $singulars[] = $newBase . ($firstUpper ? ucfirst($newSuffixEntry) : $newSuffixEntry);
                        }

                        return $singulars;
                    }

                    return $newBase . ($firstUpper ? ucfirst($newSuffix) : $newSuffix);
                }

                // Suffix is longer than word
                if ($j === $pluralLength) {
                    break;
                }
            }
        }

        // Assume that plural and singular is identical
        return $plural;
    }

    private static $pluralMap = array(
        // First entry: plural suffix, reversed
        // Second entry: length of plural suffix
        // Third entry: Whether the suffix may succeed a vocal
        // Fourth entry: Whether the suffix may succeed a consonant
        // Fifth entry: singular suffix, normal

        // bacteria (bacterium), criteria (criterion), phenomena (phenomenon)
        array('a', 1, true, true, array('on', 'um')),

        // nebulae (nebula)
        array('ea', 2, true, true, 'a'),

        // services (service)
        array('secivres', 8, true, true, 'service'),

        // mice (mouse), lice (louse)
        array('eci', 3, false, true, 'ouse'),

        // geese (goose)
        array('esee', 4, false, true, 'oose'),

        // fungi (fungus), alumni (alumnus), syllabi (syllabus), radii (radius)
        array('i', 1, true, true, 'us'),

        // men (man), women (woman)
        array('nem', 3, true, true, 'man'),

        // children (child)
        array('nerdlihc', 8, true, true, 'child'),

        // oxen (ox)
        array('nexo', 4, false, false, 'ox'),

        // indices (index), appendices (appendix), prices (price)
        array('seci', 4, false, true, array('ex', 'ix', 'ice')),

        // selfies (selfie)
        array('seifles', 7, true, true, 'selfie'),

        // movies (movie)
        array('seivom', 6, true, true, 'movie'),

        // feet (foot)
        array('teef', 4, true, true, 'foot'),

        // geese (goose)
        array('eseeg', 5, true, true, 'goose'),

        // teeth (tooth)
        array('hteet', 5, true, true, 'tooth'),

        // news (news)
        array('swen', 4, true, true, 'news'),

        // series (series)
        array('seires', 6, true, true, 'series'),

        // babies (baby)
        array('sei', 3, false, true, 'y'),

        // accesses (access), addresses (address), kisses (kiss)
        array('sess', 4, true, false, 'ss'),

        // analyses (analysis), ellipses (ellipsis), fungi (fungus),
        // neuroses (neurosis), theses (thesis), emphases (emphasis),
        // oases (oasis), crises (crisis), houses (house), bases (base),
        // atlases (atlas)
        array('ses', 3, true, true, array('s', 'se', 'sis')),

        // objectives (objective), alternative (alternatives)
        array('sevit', 5, true, true, 'tive'),

        // drives (drive)
        array('sevird', 6, false, true, 'drive'),

        // lives (life), wives (wife)
        array('sevi', 4, false, true, 'ife'),

        // moves (move)
        array('sevom', 5, true, true, 'move'),

        // hooves (hoof), dwarves (dwarf), elves (elf), leaves (leaf), caves (cave), staves (staff)
        array('sev', 3, true, true, array('f', 've', 'ff')),

        // axes (axis), axes (ax), axes (axe)
        array('sexa', 4, false, false, array('ax', 'axe', 'axis')),

        // indexes (index), matrixes (matrix)
        array('sex', 3, true, false, 'x'),

        // quizzes (quiz)
        array('sezz', 4, true, false, 'z'),

        // bureaus (bureau)
        array('suae', 4, false, true, 'eau'),

        // roses (rose), garages (garage), cassettes (cassette),
        // waltzes (waltz), heroes (hero), bushes (bush), arches (arch),
        // shoes (shoe)
        array('se', 2, true, true, array('', 'e')),

        // tags (tag)
        array('s', 1, true, true, ''),

        // chateaux (chateau)
        array('xuae', 4, false, true, 'eau'),

        // people (person)
        array('elpoep', 6, true, true, 'person'),
    );

    /**
     * @internal
     */
    public static $mutatorPrefixes = array('add', 'remove', 'set');

    /**
     * @internal
     */
    public static $accessorPrefixes = array('is', 'can', 'get', 'has');

    /**
     * @internal
     */
    public static $arrayMutatorPrefixes = array('add', 'remove');
}