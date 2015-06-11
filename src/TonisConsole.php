<?php
namespace Tonis\Tonis;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Tonis\Di\ContainerAwareInterface;

class TonisConsole extends Application
{
    /** @var Tonis */
    private $tonis;

    /**
     * @param Tonis $tonis
     */
    public function __construct(Tonis $tonis)
    {
        $this->tonis = $tonis;
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
