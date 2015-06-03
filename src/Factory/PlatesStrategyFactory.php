<?php
namespace Tonis\Mvc\Factory;

use League\Plates;
use Tonis\Di;
use Tonis\Mvc;
use Tonis\Package;
use Tonis\View;

final class PlatesStrategyFactory implements Di\ServiceFactoryInterface
{
    /**
     * @param Di\Container $di
     * @return View\Strategy\PlatesStrategy
     */
    public function createService(Di\Container $di)
    {
        $pm = $di->get(Package\Manager::class);
        $engine = new Plates\Engine();

        foreach ($pm->getPackages() as $package) {
            if ($package instanceof Mvc\Package\PackageInterface) {
                $path = realpath($package->getPath() . '/view');
                if ($path) {
                    $engine->addFolder($package->getName(), $path);
                }
            }
        }

        foreach ($di['tonis']['plates']['folders'] as $name => $path) {
            $engine->addFolder($name, $path);
        }

        return new View\Strategy\PlatesStrategy($engine);
    }
}
