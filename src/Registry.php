<?php


namespace Seacommerce\Mapper;

use Seacommerce\Mapper\Exception\DuplicateConfigurationException;
use Seacommerce\Mapper\Exception\ValidationErrorsException;

class Registry implements RegistryInterface
{
    /** @var string */
    private $scope;

    /** @var ConfigurationInterface[] */
    private $registry = [];

    /**
     * Registry constructor.
     * @param string|null $scope
     * @throws \Exception
     */
    public function __construct(?string $scope = null)
    {
        $this->scope = $scope ?? strtoupper(bin2hex(random_bytes(6)));
    }

    /**
     * @param string $source
     * @param string $target
     * @return ConfigurationInterface
     * @throws \Exception
     */
    public function add(string $source, string $target): ConfigurationInterface
    {
        $key = $this->getConfigurationKey($source, $target);
        if (isset($this->registry[$key])) {
            throw new DuplicateConfigurationException($source, $target);
        }
        $m = new Configuration($source, $target, $this->scope);
        $this->registry[$key] = $m;
        return $m;
    }

    public function has(string $source, string $target): bool
    {
        $key = $this->getConfigurationKey($source, $target);
        return isset($this->registry[$key]);
    }

    public function get(string $source, string $dest): ?ConfigurationInterface
    {
        $key = $this->getConfigurationKey($source, $dest);
        return isset($this->registry[$key]) ? $this->registry[$key] : null;
    }

    /**
     * @param bool $throw
     * @return array
     * @throws ValidationErrorsException
     */
    public function validate(bool $throw = true): array
    {
        $errors = [];
        foreach ($this->registry as $key => $config) {
            $errors = array_merge($errors, $config->validate(false));
        }
        if ($throw && !empty($errors)) {
            throw new ValidationErrorsException($errors);
        }
        return $errors;
    }

    /**
     * @inheritdoc
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->registry);
    }

    private function getConfigurationKey(string $sourceClass, string $targetClass): string
    {
        return "$sourceClass=>$targetClass";
    }
}