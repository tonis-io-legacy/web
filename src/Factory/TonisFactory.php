<?php
namespace Tonis\Web\Factory;

use Tonis\Di\Container;
use Tonis\Dispatcher\Dispatcher;
use Tonis\Event\EventManager;
use Tonis\Web\Subscriber\ApiSubscriber;
use Tonis\Web\Subscriber\BaseSubscriber;
use Tonis\Web\Subscriber\ConsoleSubscriber;
use Tonis\Web\Subscriber\WebSubscriber;
use Tonis\Web\Tonis;
use Tonis\Web\TonisConfig;
use Tonis\Web\TonisConsole;
use Tonis\Package\PackageManager;
use Tonis\Router\RouteCollection;
use Tonis\View\ViewManager;

final class TonisFactory
{
    /**
     * @param array $config
     * @return TonisConsole
     */
    public function createConsole(array $config = [])
    {
        $console = new TonisConsole($this->createTonisInstance($config));
        $console->getTonis()->di()->set(TonisConsole::class, $console, true);

        $tonis = $console->getTonis();
        $tonis->events()->subscribe(new BaseSubscriber($tonis->di()));
        $tonis->events()->subscribe(new ConsoleSubscriber($tonis->di()));

        return $console;
    }

    /**
     * @param array $config
     * @return Tonis
     */
    public function createApi(array $config = [])
    {
        $tonis = $this->createTonisInstance($config);
        $tonis->events()->subscribe(new BaseSubscriber($tonis->di()));
        $tonis->events()->subscribe(new ApiSubscriber($tonis->di()));

        return $tonis;
    }

    /**
     * @param array $config
     * @return Tonis
     */
    public function createWeb(array $config = [])
    {
        $tonis = $this->createTonisInstance($config);
        $tonis->events()->subscribe(new BaseSubscriber($tonis->di()));
        $tonis->events()->subscribe(new WebSubscriber($tonis->di()));

        return $tonis;
    }

    /**
     * @param array $config
     * @return Tonis
     */
    public function createTonisInstance(array $config = [])
    {
        $config = new TonisConfig($config);
        $di = $this->prepareServices();

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
        $di->set(PackageManager::class, new PackageManager, true);
        $di->set(EventManager::class, new EventManager, true);
        $di->set(ViewManager::class, ViewManagerFactory::class);

        return $di;
    }
}
