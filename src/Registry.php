<?php


namespace Seacommerce\Mapper;


class Registry implements RegistryInterface
{
    /** @var array */
    private $registry = [];

    /**
     * @param string $source
     * @param string $dest
     * @return MappingInterface
     * @throws \Exception
     */
    public function register(string $source, string $dest): MappingInterface
    {
        $key = $this->getKey($source, $dest);
        if (isset($this->registry[$key])) {
            // TODO: Specific exception
            throw new \Exception("Mapping from '$source' to '$dest' already exists.");
        }
        $m = new Mapping($source, $dest);
        $this->registry[$key] = $m;
        return $m;
    }

    public function has(string $source, string $dest): bool
    {
        $key = $this->getKey($source, $dest);
        return isset($this->registry[$key]);
    }

    public function get(string $source, string $dest): ?MappingInterface
    {
        $key = $this->getKey($source, $dest);
        return isset($this->registry[$key]) ? $this->registry[$key] : null;
    }

    private function getKey(string $source, string $dest): string
    {
        $key = $source . '_' . $dest;
        return $key;
    }
}