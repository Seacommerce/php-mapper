<?php
declare(strict_types=1);

namespace Seacommerce\Mapper\Test;

use Seacommerce\Mapper\Exception\AggregatedValidationErrorsException;
use Seacommerce\Mapper\Exception\DuplicateConfigurationException;
use Seacommerce\Mapper\Registry;

class RegistryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @throws \Seacommerce\Mapper\Exception\AggregatedValidationErrorsException
     */
    public function testInitialization()
    {
        $registry = new Registry('reg');

        $this->assertEquals('reg', $registry->getScope());

        $has = $registry->has(Model\PublicFields\Source::class, Model\PublicFields\Target::class);
        $this->assertFalse($has);

        $get = $registry->get(Model\PublicFields\Source::class, Model\PublicFields\Target::class);
        $this->assertNull($get);

        $errors = $registry->validate(false);
        $this->assertEmpty($errors);
    }

    public function testDuplicate()
    {
        $this->expectException(DuplicateConfigurationException::class);
        $registry = new Registry();
        $registry->add(Model\PublicFields\Source::class, Model\PublicFields\Target::class);
        $registry->add(Model\PublicFields\Source::class, Model\PublicFields\Target::class);
    }

    public function testValidateValidMappingShouldReturnNull()
    {
        $registry = new Registry();
        $registry->add(Model\PublicFields\Source::class, Model\PublicFields\Target::class)
            ->automap()
            ->ignore('dateTime', 'fixed', 'ignore');
        $exception = $registry->validate(false);
        $this->assertNull($exception);
    }

    public function testValidateInvalidMappingShouldReturnException()
    {
        $registry = new Registry();
        $registry->add(Model\PublicFields\Source::class, Model\PublicFields\Target::class)
            ->automap();
        $exception = $registry->validate(false);
        $this->assertInstanceOf(AggregatedValidationErrorsException::class, $exception);
    }
}