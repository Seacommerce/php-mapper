<?php


namespace Seacommerce\Mapper\Compiler;


use PhpParser\PrettyPrinter\Standard;
use Seacommerce\Mapper\ConfigurationInterface;

class CachedLoader implements LoaderInterface
{
    /*** @var CompilerInterface */
    private $compiler;
    /*** @var string */
    private $cacheFolder;

    /*** @var string */
    private $hashesFilename;
    /*** @var Standard */
    private $printer;

    private $hashes = [];

    public function __construct(CompilerInterface $compiler, string $cacheFolder)
    {
        $this->compiler = $compiler;
        $this->cacheFolder = $cacheFolder;
        $this->hashesFilename = $this->cacheFolder . \DIRECTORY_SEPARATOR . 'hashes.php';
        $this->printer = new Standard();
    }

    public function warmup(ConfigurationInterface $configuration): void
    {
        $this->ensureFile($configuration);
    }

    public function load(ConfigurationInterface $configuration): void
    {
        $fileName = $this->ensureFile($configuration);
        require $fileName;
    }

    private function ensureFile(ConfigurationInterface $configuration): string
    {
        $className = $configuration->getMapperClassName();
        $hash = $configuration->getHash();
        $fileName = $this->cacheFolder . DIRECTORY_SEPARATOR . $className . '.php';

        $this->loadHashes();
        $hashes = $this->hashes;

        if (!isset($hashes[$className]) || $hashes[$className] !== $hash || !file_exists($fileName)) {
            $node = $this->compiler->compile($configuration);
            $code = $classCode = $this->printer->prettyPrintFile([$node]);
            file_put_contents($fileName, $code);
            $this->hashes[$className] = $hash;
            $this->saveHashes();
        }
        return $fileName;
    }

    private function loadHashes()
    {
        if (!file_exists($this->cacheFolder)) {
            mkdir($this->cacheFolder, 0777, true);
        }
        if (!$this->hashes) {
            if (!file_exists($this->hashesFilename)) {
                $this->hashes = [];
            } else {
                $this->hashes = require $this->hashesFilename;
            }
        }
    }

    private function saveHashes()
    {
        file_put_contents($this->hashesFilename, "<?php\n\nreturn " . var_export($this->hashes, true) . ";\n");
    }

}