<?php

namespace Seacommerce\Mapper\Exception;

class ConfigurationNotFoundException extends \Exception
{
    /** @var string */
    private $sourceClass;
    /** @var string */
    private $targetClass;

    public function __construct(string $sourceClass, string $targetClass)
    {
        $this->sourceClass = $sourceClass;
        $this->targetClass = $targetClass;
        parent::__construct("Configuration for mapping from '{$sourceClass}' to '{$sourceClass}' could not be found.", 0, null);
    }

    /** @return string */
    public function getSourceClass(): string
    {
        return $this->sourceClass;
    }

    /** @return string */
    public function getTargetClass(): string
    {
        return $this->targetClass;
    }
}