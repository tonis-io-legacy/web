<?php
namespace Tonis\Mvc\Factory;

use Tonis\Di\Container;
use Tonis\Dispatcher\Dispatcher;
use Tonis\Event\EventManager;
use Tonis\Mvc\Tonis;
use Tonis\Mvc\TonisConfig;
use Tonis\Package\PackageManager;
use Tonis\Router\RouteCollection;
use Tonis\View\Strategy\PlatesStrategy;
use Tonis\View\Strategy\TwigStrategy;
use Tonis\View\ViewManager;

abstract class TonisFactory
{
    /**
     * @param array $config
     * @return Tonis
     */
    public static function fromDefaults(array $config = [])
    {
        $config = new TonisConfig($config);
        $di = new Container;

        /*
         * Services required by Tonis. We'll put them in the DiC so that they're
         * available to other factories if necessary.
         */
        $di->set(TonisConfig::class, $config);
        $di->set(PackageManager::class, PackageManagerFactory::class);
        $di->set(RouteCollection::class, new RouteCollection);
        $di->set(EventManager::class, EventManagerFactory::class);
        $di->set(Dispatcher::class, Dispatcher::class);
        $di->set(ViewManager::class, ViewManagerFactory::class);

        /*
         * Services that are created during a request lifecycle.
         */
        $di->set(PlatesStrategy::class, PlatesStrategyFactory::class);
        $di->set(TwigStrategy::class, TwigStrategyFactory::class);

        $tonis = new Tonis(
            $config,
            $di,
            $di->get(EventManager::class),
            $di->get(PackageManager::class),
            $di->get(RouteCollection::class)
        );

        $di->set(Tonis::class, $tonis);

        return $tonis;
    }
}
