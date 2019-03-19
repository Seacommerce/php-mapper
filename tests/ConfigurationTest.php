<?php
declare(strict_types=1);

namespace Seacommerce\Mapper\Test;

use Seacommerce\Mapper\Configuration;
use Seacommerce\Mapper\Exception\PropertyNotFoundException;
use Seacommerce\Mapper\Exception\ValidationErrorsException;
use Seacommerce\Mapper\Operation;
use Seacommerce\Mapper\PreparedConfiguration;
use Seacommerce\Mapper\Registry;

class ConfigurationTest extends \PHPUnit\Framework\TestCase
{
    /**
     *
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
            ->prepare()
            ->validate(false);
        $this->assertEmpty($errors);
    }

    /**
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
            ->prepare()
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

        $config = new Configuration(Model\GettersSetters\Source::class, Model\GettersSetters\Target::class, 'X');
        $preparedConfig = new PreparedConfiguration($config);
        $preparedConfig->validate();
    }

    /**
     */
    public function testAutoMap()
    {
        $this->expectException(ValidationErrorsException::class);
        $this->expectExceptionMessageRegExp("/Missing mapping for property 'dateTime'/");
        $this->expectExceptionMessageRegExp("/Missing mapping for property 'fixed'/");
        $this->expectExceptionMessageRegExp("/Missing mapping for property 'ignore'/");
        (new Configuration(Model\GettersSetters\Source::class, Model\GettersSetters\Target::class, 'X'))
            ->autoMap()
            ->prepare()
            ->validate();
    }

    /**
     */
    public function testIgnoredFields()
    {
        $errors = (new Configuration(Model\GettersSetters\Source::class, Model\GettersSetters\Target::class, 'X'))
            ->autoMap()
            ->forMembers(['dateTime', 'dateMutable', 'fixed', 'ignore'], Operation::ignore())
            ->prepare()
            ->validate();

        $this->assertEmpty($errors);
    }

    /**
     * @throws \Exception
     */
    public function testIgnorePropertyNotFound()
    {
        $this->expectException(PropertyNotFoundException::class);
        (new Configuration(Model\GettersSetters\Source::class, Model\GettersSetters\Target::class, 'X'))
            ->forMember('nonExisting', Operation::ignore())
            ->prepare()
            ->validate();
    }

    /**
     * @throws \Exception
     */
    public function testMapSourcePropertyNotFound()
    {
        $this->expectException(PropertyNotFoundException::class);
        (new Configuration(Model\GettersSetters\Source::class, Model\GettersSetters\Target::class, 'X'))
            ->forMember('dateTime', Operation::fromProperty('nonExisting'))
            ->prepare()
            ->validate();
    }

    /**
     * @throws \Exception
     */
    public function testMapTargetPropertyNotFound()
    {
        $this->expectException(PropertyNotFoundException::class);
        (new Configuration(Model\GettersSetters\Source::class, Model\GettersSetters\Target::class, 'X'))
            ->forMember('nonExisting', Operation::fromProperty('date'))
            ->prepare()
            ->validate();
    }

    /**
     * @throws PropertyNotFoundException
     * @throws ValidationErrorsException
     */
    public function testPropertyLessSourceClassShouldThrowException()
    {
        $this->expectException(ValidationErrorsException::class);
        (new Configuration(Model\None\Source::class, Model\GettersSetters\Target::class, 'X'))
            ->prepare()
            ->validate();
    }

    /**
     * @throws PropertyNotFoundException
     * @throws ValidationErrorsException
     */
    public function testPropertyLessTargetClassShouldThrowException()
    {
        (new Configuration(Model\GettersSetters\Source::class, Model\None\Target::class, 'X'))
            ->prepare()
            ->validate();
        $this->assertTrue(true);
    }

    /**
     * @throws \Exception
     */
    public function testMapFromReadOnlyOrMapFromWriteOnlyShouldThrowValidationException()
    {
        $this->expectException(ValidationErrorsException::class);
        $this->expectExceptionMessageRegExp("/Target property 'ro' is not writable./");
        $this->expectExceptionMessageRegExp("/Source property 'wo' is not readable/");

        $registry = new Registry();
        $registry->add(Model\UnReadWritable\Source::class, Model\UnReadWritable\Target::class)
            ->autoMap()
            ->prepare()
            ->validate();
    }

    /**
     * @throws \Exception
     */
    public function testMapFromInvalidPropertyShouldThrowValidationException()
    {
        $this->expectException(PropertyNotFoundException::class);
        $this->expectExceptionMessageRegExp("/Property 'nonexitingproperty' does not exist./");

        (new Configuration(Model\PublicFields\Source::class, Model\PublicFields\Target::class, 'X'))
            ->autoMap()
            ->forMember('name', Operation::fromProperty('nonexitingproperty'))
            ->prepare()
            ->validate();
    }
}