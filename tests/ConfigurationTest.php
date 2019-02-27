<?php
declare(strict_types=1);

namespace Seacommerce\Mapper\Test;

use Seacommerce\Mapper\Configuration;
use Seacommerce\Mapper\Exception\PropertyNotFoundException;
use Seacommerce\Mapper\Exception\ValidationErrorsException;
use Seacommerce\Mapper\FromProperty;

class ConfigurationTest extends \PHPUnit\Framework\TestCase
{
    public function testPublicFields()
    {
        $errors = (new Configuration(Model\PublicFields\Source::class, Model\PublicFields\Target::class, 'X'))
            ->autoMap()
            ->ignore('ignore')
            ->map(['dateTime' => 'date'])
            ->callback('callback', function () {
                return 'x';
            })
            ->constValue('fixed', 100)
            ->validate(false);
        $this->assertEmpty($errors);
    }

    public function testGettersSetters()
    {
        $errors = (new Configuration(Model\GettersSetters\Source::class, Model\GettersSetters\Target::class, 'X'))
            ->autoMap()
            ->ignore('ignore')
            ->map(['dateTime' => 'date'])
            ->custom('dateMutable', new FromProperty('dateImmutable'))
            ->callback('callback', function () {
                return 'x';
            })
            ->constValue('fixed', 100)
            ->validate(false);
        $this->assertEmpty($errors);
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
        (new Configuration(Model\GettersSetters\Source::class, Model\GettersSetters\Target::class, 'X'))
            ->validate();
    }

    public function testAutoMap()
    {
        $this->expectException(ValidationErrorsException::class);
        $this->expectExceptionMessageRegExp("/Missing mapping for property 'dateTime'/");
        $this->expectExceptionMessageRegExp("/Missing mapping for property 'fixed'/");
        $this->expectExceptionMessageRegExp("/Missing mapping for property 'ignore'/");
        (new Configuration(Model\GettersSetters\Source::class, Model\GettersSetters\Target::class, 'X'))
            ->autoMap()->validate();
    }

    public function testIgnoredFields()
    {
        $errors = (new Configuration(Model\GettersSetters\Source::class, Model\GettersSetters\Target::class, 'X'))
            ->autoMap()
            ->ignore('dateTime', 'dateMutable', 'fixed', 'ignore')
            ->validate();

        $this->assertEmpty($errors);
    }

    public function testIgnorePropertyNotFound()
    {
        $this->expectException(PropertyNotFoundException::class);
        (new Configuration(Model\GettersSetters\Source::class, Model\GettersSetters\Target::class, 'X'))
            ->ignore('nonExisting');
    }

    public function testMapSourcePropertyNotFound()
    {
        $this->expectException(PropertyNotFoundException::class);
        (new Configuration(Model\GettersSetters\Source::class, Model\GettersSetters\Target::class, 'X'))
            ->map(['dateTime' => 'nonExisting']);
    }

    public function testMapTargetPropertyNotFound()
    {
        $this->expectException(PropertyNotFoundException::class);
        (new Configuration(Model\GettersSetters\Source::class, Model\GettersSetters\Target::class, 'X'))
            ->map(['nonExisting' => 'date']);
    }

    public function testPropertyLessSourceClassShouldThrowException()
    {
        $this->expectException(PropertyNotFoundException::class);
        new Configuration(Model\None\Source::class, Model\GettersSetters\Target::class, 'X');
    }

    public function testPropertyLessTargetClassShouldThrowException()
    {
        $this->expectException(PropertyNotFoundException::class);
        new Configuration(Model\GettersSetters\Source::class, Model\None\Target::class, 'X');
    }
}