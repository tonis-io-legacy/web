<?php
namespace Tonis\Mvc\Factory;

use Tonis\Di\Container;
use Tonis\Dispatcher\Dispatcher;
use Tonis\Event\EventManager;
use Tonis\Mvc\Subscriber\ApiSubscriber;
use Tonis\Mvc\Subscriber\BootstrapSubscriber;
use Tonis\Mvc\Subscriber\WebSubscriber;
use Tonis\Mvc\Tonis;
use Tonis\Mvc\TonisConfig;
use Tonis\Package\PackageManager;
use Tonis\Router\RouteCollection;
use Tonis\View\ViewManager;

final class TonisFactory
{
    /**
     * @param array $config
     * @return Tonis
     */
    public function fromWebDefaults(array $config = [])
    {
        $config['subscribers'] = [
            BootstrapSubscriber::class => function ($di) {
                return new BootstrapSubscriber($di);
            },
            WebSubscriber::class => function ($di) {
                return new WebSubscriber($di);
            }
        ];

        return $this->createTonisInstance($config);
    }

    /**
     * @param array $config
     * @return Tonis
     */
    public function fromApiDefaults(array $config = [])
    {
        $config['subscribers'] = [
            BootstrapSubscriber::class => function ($di) {
                return new BootstrapSubscriber($di);
            },
            ApiSubscriber::class => function ($di) {
                return new ApiSubscriber($di);
            }
        ];

        return $this->createTonisInstance($config);
    }

    /**
     * @param array $config
     * @return Tonis
     */
    private function createTonisInstance(array $config)
    {
        $config = new TonisConfig($config);
        $di = $this->prepareServices($config);

        $di->set(TonisConfig::class, $config, true);

        $tonis = new Tonis(
            $config,
            $di,
            $di->get(EventManager::class),
            $di->get(PackageManager::class),
            $di->get(RouteCollection::class)
        );

        $di->set(Tonis::class, $tonis, true);

        return $tonis;
    }

    /**
     * Prepares services required by Tonis. We'll put them in the DIC so that they're
     * available to other factories if necessary.
     *
     * @return Container
     */
    private function prepareServices()
    {
        $di = new Container;

        $di->set(RouteCollection::class, new RouteCollection, true);
        $di->set(Dispatcher::class, new Dispatcher, true);
        $di->set(PackageManager::class, PackageManagerFactory::class);
        $di->set(EventManager::class, EventManagerFactory::class);
        $di->set(ViewManager::class, ViewManagerFactory::class);

        return $di;
    }
}
