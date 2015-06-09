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
    public function fromDefaults(array $config = [])
    {
        $config['subscribers'] = [
            BootstrapSubscriber::class => function ($di) {
                return new BootstrapSubscriber($di);
            },
            WebSubscriber::class => function ($di) {
                return new WebSubscriber($di);
            }
        ];

        $di = $this->prepareServices($config);
        $config = new TonisConfig($config);

        $di->setService(TonisConfig::class, $config);
        return $this->createTonisInstance($di, $config);
    }

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

        $di = $this->prepareServices($config);
        $config = new TonisConfig($config);

        $di->setService(TonisConfig::class, $config);

        return $this->createTonisInstance($di, $config);
    }

    /**
     * @param Container $di
     * @param TonisConfig $config
     * @return Tonis
     */
    private function createTonisInstance(Container $di, TonisConfig $config)
    {
        $tonis = new Tonis(
            $config,
            $di,
            $di->get(EventManager::class),
            $di->get(PackageManager::class),
            $di->get(RouteCollection::class)
        );

        $di->setService(Tonis::class, $tonis);

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

        $di->setService(RouteCollection::class, new RouteCollection);
        $di->setService(Dispatcher::class, new Dispatcher);
        $di->set(PackageManager::class, PackageManagerFactory::class);
        $di->set(EventManager::class, EventManagerFactory::class);
        $di->set(ViewManager::class, ViewManagerFactory::class);

        return $di;
    }
}
