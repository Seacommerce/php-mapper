<?php


namespace Seacommerce\Mapper\Exception;

class InvalidArgumentException extends \InvalidArgumentException
{
    /**
     * @var string
     */
    private $argument;

    public function __construct(string $argument, string $message = null)
    {
        $m = "Invalid value for argument '$argument'.";
        if (!empty($message)) {
            $m .= ' ' . $message;
        }
        parent::__construct($m, 0, null);
        $this->argument = $argument;
    }

    /**
     * @return string
     */
    public function getArgument(): string
    {
        return $this->argument;
    }
}