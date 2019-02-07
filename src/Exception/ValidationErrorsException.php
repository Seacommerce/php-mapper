<?php


namespace Seacommerce\Mapper\Exception;


class ValidationErrorsException extends \Exception
{
    /** @var string */
    private $sourceClass;

    /** @var string */
    private $targetClass;

    /** @var string[] */
    private $errors;

    /**
     * ValidationErrorsException constructor.
     * @param string $sourceClass
     * @param string $targetClass
     * @param string[] $errors
     */
    public function __construct(string $sourceClass, string $targetClass, array $errors)
    {
        $this->errors = $errors;
        $m = "Mapping validation errors for {$sourceClass} -> {$targetClass}\n\n  - ";
        $m .= join("\n  - ", $errors);
        parent::__construct($m, 0, null);
    }

    /**
     * @return string
     */
    public function getSourceClass(): string
    {
        return $this->sourceClass;
    }

    /**
     * @return string
     */
    public function getTargetClass(): string
    {
        return $this->targetClass;
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}