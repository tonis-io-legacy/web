<?php
namespace Tonis\Mvc\Factory;

use Tonis\Mvc\Subscriber\ConsoleSubscriber;
use Tonis\Mvc\TonisConsole;

abstract class TonisConsoleFactory
{
    /**
     * @param array $config
     * @return TonisConsole
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
