<?php
namespace Tonis\Web;

use Tonis\Di\Container;
use Tonis\Dispatcher\Dispatcher;
use Tonis\Event\EventManager;
use Tonis\Router\Router;
use Tonis\Web\Factory\ViewManagerFactory;
use Tonis\Web\Subscriber\ApiSubscriber;
use Tonis\Web\Subscriber\BaseSubscriber;
use Tonis\Web\Subscriber\ConsoleSubscriber;
use Tonis\Web\Subscriber\WebSubscriber;
use Tonis\Package\PackageManager;
use Tonis\Router\RouteCollection;
use Tonis\View\ViewManager;

final class AppFactory
{
    /**
     * @param array $config
     * @return Console
     */
    public function createConsole(array $config = [])
    {
        $console = new Console($this->create($config));
        $console->getApp()->getServiceContainer()->set(Console::class, $console, true);

        $app = $console->getApp();
        $app->getEventManager()->subscribe(new BaseSubscriber($app->getServiceContainer()));
        $app->getEventManager()->subscribe(new ConsoleSubscriber($app->getServiceContainer()));

        return $console;
    }

    /**
     * @param array $config
     * @return App
     */
    public function createApi(array $config = [])
    {
        $app = $this->create($config);
        $app->getEventManager()->subscribe(new BaseSubscriber($app->getServiceContainer()));
        $app->getEventManager()->subscribe(new ApiSubscriber($app->getServiceContainer()));

        return $app;
    }

    /**
     * @param array $config
     * @return App
     */
    public function createWeb(array $config = [])
    {
        $app = $this->create($config);
        $app->getEventManager()->subscribe(new BaseSubscriber($app->getServiceContainer()));
        $app->getEventManager()->subscribe(new WebSubscriber($app->getServiceContainer()));

        return $app;
    }

    /**
     * @param array $config
     * @return App
     */
    public function create(array $config = [])
    {
        $config = new AppConfig($config);
        $services = $this->prepareServices();

        $services->set(AppConfig::class, $config, true);

        $app = new App(
            $config,
            $services,
            $services->get(EventManager::class),
            $services->get(PackageManager::class),
            $services->get(Router::class)
        );

        $services->set(App::class, $app, true);

        return $app;
    }

    /**
     * Prepares services required by Tonis. We'll put them in the DIC so that they're
     * available to other factories if necessary.
     *
     * @return Container
     */
    private function prepareServices()
    {
        $services = new Container;

        $services->set(Router::class, new Router, true);
        $services->set(Dispatcher::class, new Dispatcher, true);
        $services->set(PackageManager::class, new PackageManager, true);
        $services->set(EventManager::class, new EventManager, true);
        $services->set(ViewManager::class, ViewManagerFactory::class);

        return $services;
    }
}
