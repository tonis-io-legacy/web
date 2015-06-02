<?php
namespace Tonis\Mvc\Factory;

use Tonis\Di;
use Tonis\Mvc\Package\PackageInterface as TonisPackageInterface;
use Tonis\Mvc\Tonis;
use Tonis\Package;

class PackageManagerFactory implements Di\ServiceFactoryInterface
{
    /** @var array */
    private $packages;
    /** @var Tonis */
    private $tonis;

    /**
     * @param Tonis $tonis
     * @param array $packages
     */
    public function __construct(Tonis $tonis, array $packages)
    {
        $this->tonis = $tonis;
        $this->packages = $packages;
    }

    /**
     * @param Di\Container $di
     * @return mixed
     */
    public function createService(Di\Container $di)
    {
        $pm = new Package\Manager();
        $pm->add('Tonis\\Mvc');

        foreach ($this->packages as $package) {
            if ($package[0] == '?') {
                if (!$this->tonis->isDebugEnabled()) {
                    continue;
                }
                $package = substr($package, 1);
            }
            $pm->add($package);
        }
        $pm->load();

        $config = $pm->getMergedConfig();
        foreach ($config as $key => $value) {
            $di[$key] = $value;
        }

        foreach ($pm->getPackages() as $package) {
            if ($package instanceof TonisPackageInterface) {
                $package->configureDi($di);
                $package->configureRoutes($this->tonis->getRouteCollection());
                $package->bootstrap($this->tonis);
            }
        }

        return $pm;
    }
}
