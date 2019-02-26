<?php


namespace Seacommerce\Mapper;

use Seacommerce\Mapper\Exception\AggregatedValidationErrorsException;
use Seacommerce\Mapper\Exception\DuplicateConfigurationException;

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
     * @return string
     */
    public function getScope(): string
    {
        return $this->scope;
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
        return $this->get($source, $target) !== null;
    }

    public function get(string $source, string $dest): ?ConfigurationInterface
    {
        $s = $source;
        for (; ;) {
            $key = $this->getConfigurationKey($s, $dest);
            $configuration = $this->registry[$key] ?? null;
            if ($configuration !== null && (!isset($super) || $configuration->getAllowMapFromSubClass())) {
                return $configuration;
            }
            $super = get_parent_class($s);
            if ($super) {
                $s = $super;
            } else {
                return null;
            }
        }
        return null;
    }

    /**
     * @param bool $throw
     * @return AggregatedValidationErrorsException|null
     * @throws AggregatedValidationErrorsException
     */
    public function validate(bool $throw = true): ?AggregatedValidationErrorsException
    {
        $all = [];
        foreach ($this->registry as $key => $config) {
            $all[] = $config->validate(false);
        }
        $all = array_filter($all);
        if (empty($all)) {
            return null;
        }
        $ex = new AggregatedValidationErrorsException($all);
        if ($throw) {
            throw $ex;
        }
        return $ex;
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