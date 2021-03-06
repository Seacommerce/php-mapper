<?php


namespace Seacommerce\Mapper;

use Seacommerce\Mapper\Exception\AggregatedValidationErrorsException;
use Seacommerce\Mapper\Exception\DuplicateConfigurationException;
use Seacommerce\Mapper\ValueConverter\DateTimeConverter;
use Seacommerce\Mapper\ValueConverter\DateTimeImmutableConverter;
use Seacommerce\Mapper\ValueConverter\ValueConverterInterface;

class Registry implements RegistryInterface
{
    /** @var string */
    private $scope;

    /** @var ConfigurationInterface[] */
    private $registry = [];

    /**
     * @var callable[][]|ValueConverterInterface[]
     */
    private $valueConverters = [];

    /**
     * Registry constructor.
     * @param string|null $scope
     */
    public function __construct(?string $scope = null)
    {
        /** @noinspection PhpUnhandledExceptionInspection */
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
     * @throws DuplicateConfigurationException
     */
    public function add(string $source, string $target): ConfigurationInterface
    {
        $key = $this->getConfigurationKey($source, $target);
        if (isset($this->registry[$key])) {
            throw new DuplicateConfigurationException($source, $target);
        }
        $m = new Configuration($source, $target, $this->scope, $this->valueConverters);
        $m->setRegistry($this);
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
     * @throws Exception\PropertyNotFoundException
     * @throws Exception\ValidationErrorsException
     */
    public function validate(bool $throw = true): ?AggregatedValidationErrorsException
    {
        $all = [];
        foreach ($this->registry as $key => $config) {
            $all[] = $config->prepare()->validate(false);
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
     * @return \ArrayIterator[Configuration]
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->registry);
    }

    /**
     * @param string $fromType
     * @param string $toType
     * @param ValueConverterInterface|callable $converter
     */
    public function registerValueConverter(string $fromType, string $toType, $converter) : void
    {
        if (!is_callable($converter) && !($converter instanceof ValueConverterInterface)) {
            throw new \InvalidArgumentException("Invalid type for 'converter'. Expected callable or ValueConverterInterface.");
        }
        $this->valueConverters[$fromType][$toType] = $converter;
    }

    public function registerDefaultValueConverters() : void
    {
        $this->registerValueConverter(\DateTime::class, \DateTimeImmutable::class, DateTimeConverter::toImmutable());
        $this->registerValueConverter(\DateTime::class, 'int', DateTimeConverter::toTimestamp());
        $this->registerValueConverter('int', \DateTime::class, DateTimeConverter::fromTimestamp());

        $this->registerValueConverter(\DateTimeImmutable::class, \DateTime::class, DateTimeImmutableConverter::toMutable());
        $this->registerValueConverter(\DateTimeImmutable::class, 'int', DateTimeImmutableConverter::toTimestamp());
        $this->registerValueConverter('int', \DateTimeImmutable::class, DateTimeImmutableConverter::fromTimestamp());
    }

    public function getValueConverter(string $fromType, string $toType)
    {
        return $this->valueConverters[$fromType][$toType] ?? null;
    }

    private function getConfigurationKey(string $sourceClass, string $targetClass): string
    {
        return "$sourceClass=>$targetClass";
    }
}