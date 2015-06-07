<?php
namespace Tonis\Mvc\Factory;

use Tonis\Di\Container;
use Tonis\Dispatcher\Dispatcher;
use Tonis\Event\EventManager;
use Tonis\Mvc\Subscriber\ConsoleSubscriber;
use Tonis\Mvc\Tonis;
use Tonis\Mvc\TonisConsole;
use Tonis\Package\PackageManager;
use Tonis\Router\RouteCollection;
use Tonis\View\Strategy\PlatesStrategy;
use Tonis\View\Strategy\TwigStrategy;
use Tonis\View\ViewManager;

abstract class TonisConsoleFactory
{
    /**
     * @param array $config
     * @return Tonis
     */
    public static function fromDefaults(array $config = [])
    {
        $tonis = TonisFactory::fromDefaults($config);
        $tonis->events()->subscribe(new ConsoleSubscriber($tonis->di));

        $console = new TonisConsole($tonis);

        $di = $tonis->di();
        $di->set(TonisConsole::class, $console);

        $tonis->bootstrap();

        return $console;
    }
}
