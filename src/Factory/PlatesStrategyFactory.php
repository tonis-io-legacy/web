<?php
namespace Tonis\Mvc\Factory;

use League\Plates;
use Tonis\Di\Container;
use Tonis\Package\PackageManager;
use Tonis\View\Strategy\PlatesStrategy;

final class PlatesStrategyFactory extends AbstractViewStrategyFactory
{
    /**
     * @param Container $di
     * @return PlatesStrategy
     */
    public function __invoke(Container $di)
    {
        $engine = new Plates\Engine();
        $pm = $di->get(PackageManager::class);

        foreach ($this->getViewPaths($pm) as $name => $path) {
            $engine->addFolder($name, $path);
        }

        $config = $pm->getMergedConfig()['mvc']['plates']['folders'];
        foreach ($config as $name => $path) {
            $engine->addFolder($name, $path);
        }

        return new PlatesStrategy($engine);
    }
}
