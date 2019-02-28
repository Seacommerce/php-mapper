<?php
declare(strict_types=1);

namespace Seacommerce\Mapper\Test;

use Seacommerce\Mapper\Configuration;
use Seacommerce\Mapper\Exception\PropertyNotFoundException;
use Seacommerce\Mapper\Exception\ValidationErrorsException;
use Seacommerce\Mapper\Operation;

class ConfigurationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @throws PropertyNotFoundException
     */
    public function testPublicFields()
    {
        $errors = (new Configuration(Model\PublicFields\Source::class, Model\PublicFields\Target::class, 'X'))
            ->autoMap()
            ->forMember('ignore', Operation::ignore())
            ->forMember('dateTime', Operation::fromProperty('date'))
            ->forMember('dateTime', Operation::ignore())
            ->forMember('callback', Operation::mapFrom(function () {
                return 'x';
            }))
            ->forMember('fixed', Operation::setTo(100))
            ->validate(false);
        $this->assertEmpty($errors);
    }

    /**
     * @throws PropertyNotFoundException
     */
    public function testGettersSetters()
    {
        $errors = (new Configuration(Model\GettersSetters\Source::class, Model\GettersSetters\Target::class, 'X'))
            ->autoMap()
            ->forMember('ignore', Operation::ignore())
            ->forMember('dateTime', Operation::fromProperty('date'))
            ->forMember('dateMutable', Operation::fromProperty('dateImmutable'))
            ->forMember('callback', Operation::mapFrom(function () {
                return 'x';
            }))
            ->forMember('fixed', Operation::setTo(100))
            ->validate(false);
        $this->assertEmpty($errors);
    }

    /**
     * @throws PropertyNotFoundException
     * @throws ValidationErrorsException
     */
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

    /**
     * @throws PropertyNotFoundException
     */
    public function testAutoMap()
    {
        $this->expectException(ValidationErrorsException::class);
        $this->expectExceptionMessageRegExp("/Missing mapping for property 'dateTime'/");
        $this->expectExceptionMessageRegExp("/Missing mapping for property 'fixed'/");
        $this->expectExceptionMessageRegExp("/Missing mapping for property 'ignore'/");
        (new Configuration(Model\GettersSetters\Source::class, Model\GettersSetters\Target::class, 'X'))
            ->autoMap()->validate();
    }

    /**
     * @throws PropertyNotFoundException
     */
    public function testIgnoredFields()
    {
        $errors = (new Configuration(Model\GettersSetters\Source::class, Model\GettersSetters\Target::class, 'X'))
            ->autoMap()
            ->forMembers(['dateTime', 'dateMutable', 'fixed', 'ignore'], Operation::ignore())
            ->validate();

        $this->assertEmpty($errors);
    }

    /**
     * @throws PropertyNotFoundException
     * @throws \Exception
     */
    public function testIgnorePropertyNotFound()
    {
        $this->expectException(PropertyNotFoundException::class);
        (new Configuration(Model\GettersSetters\Source::class, Model\GettersSetters\Target::class, 'X'))
            ->ignore('nonExisting');
    }

    /**
     * @throws PropertyNotFoundException
     * @throws \Exception
     */
    public function testMapSourcePropertyNotFound()
    {
        $this->expectException(PropertyNotFoundException::class);
        (new Configuration(Model\GettersSetters\Source::class, Model\GettersSetters\Target::class, 'X'))
            ->map(['dateTime' => 'nonExisting']);
    }

    /**
     * @throws PropertyNotFoundException
     * @throws \Exception
     */
    public function testMapTargetPropertyNotFound()
    {
        $this->expectException(PropertyNotFoundException::class);
        (new Configuration(Model\GettersSetters\Source::class, Model\GettersSetters\Target::class, 'X'))
            ->map(['nonExisting' => 'date']);
    }

    /**
     * @throws PropertyNotFoundException
     */
    public function testPropertyLessSourceClassShouldThrowException()
    {
        $this->expectException(PropertyNotFoundException::class);
        new Configuration(Model\None\Source::class, Model\GettersSetters\Target::class, 'X');
    }

    /**
     * @throws PropertyNotFoundException
     */
    public function testPropertyLessTargetClassShouldThrowException()
    {
        $this->expectException(PropertyNotFoundException::class);
        new Configuration(Model\GettersSetters\Source::class, Model\None\Target::class, 'X');
    }
}