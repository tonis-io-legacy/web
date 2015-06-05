<?php
namespace Tonis\Mvc;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Tonis\Di\ContainerAwareInterface;

class TonisConsole extends Application
{
    /** @var Tonis */
    private $tonis;

    /**
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        if (!isset($config['subscribers'])) {
            $config['subscribers'] = [new Subscriber\ConsoleSubscriber($this)];
        }
        $this->tonis = new Tonis($config);
        parent::__construct();
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
