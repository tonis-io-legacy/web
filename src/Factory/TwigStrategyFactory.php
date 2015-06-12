<?php
namespace Tonis\Web\Factory;

use Tonis\Di\Container;
use Tonis\Di\ContainerUtil;
use Tonis\Package\PackageManager;
use Tonis\View\Strategy\TwigStrategy;

final class TwigStrategyFactory extends AbstractViewStrategyFactory
{
    /**
     * @param Container $services
     * @return TwigStrategy
     */
    public function __invoke(Container $services)
    {
        $loader = new \Twig_Loader_Filesystem();
        $pm = $services->get(PackageManager::class);

        foreach ($this->getViewPaths($pm) as $name => $path) {
            $loader->addPath($path, $name);
        }

        $config = $services['config']['twig'];
        foreach ($config['namespaces'] as $namespace => $path) {
            $loader->addPath($path, $namespace);
        }

        $twig = new \Twig_Environment($loader, $config['options']);

        foreach ($config['extensions'] as $extension) {
            $twig->addExtension(ContainerUtil::get($services, $extension));
        }

        return new TwigStrategy($twig);
    }
}
