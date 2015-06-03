<?php
namespace Tonis\Mvc\Factory;

use Tonis\Di;
use Tonis\Mvc;
use Tonis\Package;
use Tonis\View;

final class TwigStrategyFactory implements Di\ServiceFactoryInterface
{
    /**
     * @param Di\Container $di
     * @return View\Strategy\TwigStrategy
     */
    public function createService(Di\Container $di)
    {
        $pm = $di->get(Package\Manager::class);

        $paths = [];
        foreach ($pm->getPackages() as $package) {
            if ($package instanceof Mvc\Package\PackageInterface) {
                $path = realpath($package->getPath() . '/view');
                if ($path) {
                    $paths[$package->getName()] = $path;
                }
            }
        }

        $loader = new \Twig_Loader_Filesystem();

        foreach ($paths as $namespace => $path) {
            $loader->addPath($path, $namespace);
        }


        foreach ($di['tonis']['twig']['namespaces'] as $namespace => $path) {
            $loader->addPath($path, $namespace);
        }

        $twig = new \Twig_Environment($loader, $di['tonis']['twig']['options']);

        foreach ($di['tonis']['twig']['extensions'] as $extension) {
            $twig->addExtension(Di\ContainerUtil::get($di, $extension));
        }

        return new View\Strategy\TwigStrategy($twig);
    }
}
