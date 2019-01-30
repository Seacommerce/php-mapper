<?php
declare(strict_types=1);

namespace Seacommerce\Mapper\Test;

use Seacommerce\Mapper\Exception\ValidationErrorsException;
use Seacommerce\Mapper\Registry;

class ConfigurationTest extends \PHPUnit\Framework\TestCase
{
    public function testPublicFields()
    {
        $registry = new Registry();
        $registry->add(Model\PublicFields\Source::class, Model\PublicFields\Target::class)
            ->automap()
            ->ignore('ignore')
            ->map(['dateTime' => 'date'])
            ->callback('callback', function () {
                return 'x';
            })
            ->constValue('fixed', 100);

        $registry->validate();
    }

    public function testGettersSetters()
    {
        $registry = new Registry();
        $registry->add(Model\GettersSetters\Source::class, Model\GettersSetters\Target::class)
            ->automap()
            ->ignore('ignore')
            ->map(['dateTime' => 'date'])
            ->callback('callback', function () {
                return 'x';
            })
            ->constValue('fixed', 100);

        $registry->validate();
    }

    public function testEmptyConfiguration()
    {
        $this->expectException(ValidationErrorsException::class);
        $this->expectExceptionMessageRegExp("/Missing mapping for property 'id'/");
        $this->expectExceptionMessageRegExp("/Missing mapping for property 'name'/");
        $this->expectExceptionMessageRegExp("/Missing mapping for property 'dateTime'/");
        $this->expectExceptionMessageRegExp("/Missing mapping for property 'fixed'/");
        $this->expectExceptionMessageRegExp("/Missing mapping for property 'callback'/");
        $this->expectExceptionMessageRegExp("/Missing mapping for property 'ignore'/");
        $registry = new Registry();
        $registry->add(Model\GettersSetters\Source::class, Model\GettersSetters\Target::class);
        $registry->validate();
    }

    public function testAutoMap()
    {
        $this->expectException(ValidationErrorsException::class);
        $this->expectExceptionMessageRegExp("/Missing mapping for property 'dateTime'/");
        $this->expectExceptionMessageRegExp("/Missing mapping for property 'fixed'/");
        $this->expectExceptionMessageRegExp("/Missing mapping for property 'ignore'/");
        $registry = new Registry();
        $registry->add(Model\GettersSetters\Source::class, Model\GettersSetters\Target::class)
            ->automap();
        $registry->validate();
    }

    public function testIgnoredFields()
    {
        $registry = new Registry();
        $registry->add(Model\GettersSetters\Source::class, Model\GettersSetters\Target::class)
            ->automap()
            ->ignore('dateTime', 'fixed', 'ignore');
        $registry->validate();
    }
}