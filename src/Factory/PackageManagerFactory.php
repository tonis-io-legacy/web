<?php
namespace Tonis\Mvc\Factory;

use Tonis\Di;
use Tonis\Package;

final class PackageManagerFactory implements Di\ServiceFactoryInterface
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
     * @param Di\Container $di
     * @return mixed
     */
    public function createService(Di\Container $di)
    {
        $pm = new Package\Manager();
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
        return $pm;
    }
}
