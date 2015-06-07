<?php
namespace Tonis\Mvc\Factory;

use Tonis\Di\Container;
use Tonis\Mvc\Package;
use Tonis\Mvc\TonisConfig;
use Tonis\Package\PackageManager;

final class PackageManagerFactory
{
    /**
     * @param Container $di
     * @return PackageManager
     */
    public function __invoke(Container $di)
    {
        /** @var TonisConfig $config */
        $config = $di->get(TonisConfig::class);
        $pm = new PackageManager;

        $pm->add(Package::class);

        foreach ($config->getPackages() as $package) {
            $pm->add($package);
        }

        return $pm;
    }
}
