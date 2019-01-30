<?php


namespace Seacommerce\Mapper\Exception;

class ClassNotFoundException extends \Exception
{
    /** @var string */
    private $class;

    public function __construct(string $sourceClass)
    {
        parent::__construct("Class '{$sourceClass}' could not be found.", 0, null);
    }

    /**
     * @return string
     */
    public function getClass(): string
    {
        return $this->class;
    }
}