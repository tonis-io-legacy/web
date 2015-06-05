<?php
namespace Tonis\Mvc;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Tonis\Di\ContainerAwareInterface;
use Tonis\Mvc\Subscriber\ConsoleSubscriber;
use Tonis\Package\PackageManager;

class TonisConsole extends Application
{
    /** @var Tonis */
    private $tonis;

    /**
     * @param array $config
     * @return TonisConsole
     */
    public static function createWithDefaults(array $config = [])
    {
        $tonis = Tonis::createWithDefaults($config);
        $di = $tonis->di();

        $console = new self;
        $di->set(ConsoleSubscriber::class, new ConsoleSubscriber($console, $di->get(PackageManager::class)));

        return $console;
    }

    /**
     * @return Tonis
     */
    public function getTonis()
    {
        return $this->tonis;
    }

    /**
     * {@inheritDoc}
     */
    public function add(Command $command)
    {
        if ($command instanceof ContainerAwareInterface) {
            $command->setDi($this->tonis->di());
        }
        return parent::add($command);
    }
}
