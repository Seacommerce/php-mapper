<?php
declare(strict_types=1);

namespace Seacommerce\Mapper\Test;

use Seacommerce\Mapper\Exception\DuplicateConfigurationException;
use Seacommerce\Mapper\Registry;

class RegistryTest extends \PHPUnit\Framework\TestCase
{
    public function testInitialization()
    {
        $registry = new Registry();

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
}