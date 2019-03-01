<?php


namespace Seacommerce\Mapper\Compiler;


use PhpParser\PrettyPrinter\Standard;
use Seacommerce\Mapper\ConfigurationInterface;

class EvalLoader implements LoaderInterface
{
    /*** @var CompilerInterface */
    private $compiler;
    /*** @var Standard */
    private $printer;
    /** @var array */
    private $hashes = [];

    public function __construct(CompilerInterface $compiler)
    {
        $this->compiler = $compiler;
        $this->printer = new Standard();
    }

    public function load(ConfigurationInterface $configuration): void
    {
        $className = $configuration->getMapperClassName();
        $hash = $configuration->getHash();
        if (!isset($this->hashes[$className]) || $this->hashes[$className] !== $hash) {
            $node = $this->compiler->compile($configuration);
            $code = $classCode = $this->printer->prettyPrint([$node]);
            eval($code);
            $this->hashes[$className] = $hash;
        }
    }

    public function warmup(ConfigurationInterface $configuration): void
    {
        // TODO: Implement warmup() method.
    }
}