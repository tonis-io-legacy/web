<?php
namespace Tonis\Mvc\Factory;

use Tonis\Di\Container;
use Tonis\Di\ServiceFactoryInterface;
use Tonis\Package\PackageManager;

final class PackageManagerFactory implements ServiceFactoryInterface
{
    /** @var array */
    private $packages;
    /** @var bool */
    private $debug;

    /**
     * @param bool $debug
     * @param array $packages
     */
    public function __construct($debug, array $packages)
    {
        $this->debug = $debug;
        $this->packages = $packages;
    }

    /**
     * @param Container $di
     * @return mixed
     */
    public function createService(Container $di)
    {
        $pm = new PackageManager();
        $pm->add('Tonis\\Mvc');

        foreach ($this->packages as $package) {
            if ($package[0] == '?') {
                if (!$this->debug) {
                    continue;
                }
                $package = substr($package, 1);
            }
            $pm->add($package);
        }

        $pm->load();

        $di->set(PackageManager::class, $pm);

        return $pm;
    }
}
