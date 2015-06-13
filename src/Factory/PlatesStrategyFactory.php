<?php
namespace Tonis\Web\Factory;

use League\Plates;
use Tonis\Di\Container;
use Tonis\Di\ContainerUtil;
use Tonis\Package\PackageManager;
use Tonis\View\Strategy\PlatesStrategy;

final class PlatesStrategyFactory extends AbstractViewStrategyFactory
{
    /**
     * @param Container $services
     * @return PlatesStrategy
     */
    public function __invoke(Container $services)
    {
        $engine = new Plates\Engine();
        $pm = $services->get(PackageManager::class);

        foreach ($this->getViewPaths($pm) as $name => $path) {
            $engine->addFolder($name, $path);
        }

        $config = $services['config']['plates'];
        foreach ($config['folders'] as $name => $path) {
            $engine->addFolder($name, $path);
        }

        foreach ($config['extensions'] as $extension) {
            $engine->loadExtension(ContainerUtil::get($services, $extension));
        }

        return new PlatesStrategy($engine);
    }
}
