<?php
namespace Tonis\Mvc\Factory;

use Tonis\Mvc\Package\PackageInterface;
use Tonis\Package\PackageManager;

abstract class AbstractViewStrategyFactory
{
    /**
     * @param PackageManager $packageManager
     * @return array
     */
    protected function getViewPaths(PackageManager $packageManager)
    {
        $paths = [];
        foreach ($packageManager->getPackages() as $package) {
            if ($package instanceof PackageInterface) {
                $path = realpath($package->getPath() . '/view');
                if ($path) {
                    $paths[$package->getName()] = $path;
                }
            }
        }

        return $paths;
    }
}
