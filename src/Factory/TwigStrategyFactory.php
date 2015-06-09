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
    public function __invoke(Container $di)
    {
        $loader = new \Twig_Loader_Filesystem();
        $pm = $di->get(PackageManager::class);

        foreach ($this->getViewPaths($pm) as $name => $path) {
            $loader->addPath($path, $name);
        }

        $config = $pm->getMergedConfig()['twig'];
        foreach ($config['namespaces'] as $namespace => $path) {
            $loader->addPath($path, $namespace);
        }

        $twig = new \Twig_Environment($loader, $config['options']);

        foreach ($config['extensions'] as $extension) {
            $twig->addExtension(ContainerUtil::get($di, $extension));
        }

        return new TwigStrategy($twig);
    }
}
