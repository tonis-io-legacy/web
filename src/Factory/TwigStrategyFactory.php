<?php
namespace Tonis\Mvc\Factory;

use Tonis\Di\Container;
use Tonis\Di\ContainerUtil;
use Tonis\Package\PackageManager;
use Tonis\View\Strategy\TwigStrategy;

final class TwigStrategyFactory extends AbstractViewStrategyFactory
{
    /**
     * @param Container $di
     * @return TwigStrategy
     */
    public function createService(Container $di)
    {
        $loader = new \Twig_Loader_Filesystem();

        foreach ($this->getViewPaths($di->get(PackageManager::class)) as $name => $path) {
            $loader->addPath($path, $name);
        }

        foreach ($di['tonis']['twig']['namespaces'] as $namespace => $path) {
            $loader->addPath($path, $namespace);
        }

        $twig = new \Twig_Environment($loader, $di['tonis']['twig']['options']);

        foreach ($di['tonis']['twig']['extensions'] as $extension) {
            $twig->addExtension(ContainerUtil::get($di, $extension));
        }

        return new TwigStrategy($twig);
    }
}
