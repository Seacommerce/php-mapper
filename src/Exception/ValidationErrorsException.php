<?php


namespace Seacommerce\Mapper\Exception;


class ValidationErrorsException extends \Exception
{
    /** @var array */
    private $errors;

    public function __construct(array $errors)
    {
        // TODO: Custom class for validation errors that holds some extra info.
        $this->errors = $errors;
        $message = join("\n\t- ", $errors);
        parent::__construct("Mapping validation errors: \n\t- {$message}", 0, null);
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}