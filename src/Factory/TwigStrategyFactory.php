<?php
namespace Tonis\Mvc\Factory;

use Tonis\Di;
use Tonis\View;

final class TwigStrategyFactory implements Di\ServiceFactoryInterface
{
    /**
     * @param Di\Container $di
     * @return View\Strategy\PlatesStrategy
     */
    public function createService(Di\Container $di)
    {
        /** @var Tonis $tonis */
        $tonis = $di->get(Tonis::class);
        $pm = $tonis->getPackageManager();

        $paths = [];
        foreach ($pm->getPackages() as $package) {
            if ($package instanceof PackageInterface) {
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
            $twig->addExtension(ContainerUtil::get($di, $extension));
        }

        return new View\Strategy\TwigStrategy($twig);
    }
}
