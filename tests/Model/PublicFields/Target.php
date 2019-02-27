<?php


namespace Seacommerce\Mapper\Test\Model\PublicFields;


use DateTime;

class Target
{
    /** @var int|null */
    public $id;

    /** @var string|null */
    public $name;

    /** @var DateTime */
    public $dateTime;

    /** @var string|null */
    public $callback;

    /** @var string|null */
    public $fixed;

    /** @var string|null */
    public $ignore;
}