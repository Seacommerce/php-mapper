<?php


namespace Seacommerce\Mapper\Exception;


class AggregatedValidationErrorsException extends \Exception
{
    /** @var ValidationErrorsException[] */
    private $exceptions;

    /**
     * @param ValidationErrorsException[] $exceptions
     */
    public function __construct(array $exceptions)
    {
        $this->exceptions = $exceptions;
        $all = [];
        foreach ($exceptions as $x) {
            $all[] = $x->getMessage();
        }
        parent::__construct(join('\n\n', $all), 0, null);

    }

    /**
     * @return ValidationErrorsException[]
     */
    public function getExceptions(): array
    {
        return $this->exceptions;
    }

    /**
     * @param ValidationErrorsException[] $exceptions
     */
    public function setExceptions(array $exceptions): void
    {
        $this->exceptions = $exceptions;
    }
}