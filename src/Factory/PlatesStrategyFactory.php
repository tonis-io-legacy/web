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
    public function createService(Container $di)
    {
        $engine = new Plates\Engine();

        foreach ($this->getViewPaths($di->get(PackageManager::class)) as $name => $path) {
            $engine->addFolder($name, $path);
        }

        foreach ($di['tonis']['plates']['folders'] as $name => $path) {
            $engine->addFolder($name, $path);
        }

        return new PlatesStrategy($engine);
    }
}
