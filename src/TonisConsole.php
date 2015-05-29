<?php
namespace Tonis\Mvc;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tonis\Di\ContainerAwareInterface;
use Tonis\Mvc\Hook;

class TonisConsole extends Application
{
    /** @var Tonis */
    private $tonis;

    /**
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        if (!isset($config['hooks'])) {
            $config['hooks'] = [
                Hook\DefaultTonisHook::class,
                new Hook\DefaultConsoleHook($this),
            ];
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
            $command->setDi($this->tonis->getDi());
        }
        return parent::add($command);
    }

    /**
     * {@inheritDoc}
     */
    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        $this->tonis->bootstrap();
        return parent::run($input, $output);
    }
}
