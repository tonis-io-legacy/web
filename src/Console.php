<?php
namespace Tonis\Web;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Tonis\Di\ContainerAwareInterface;

class Console extends Application
{
    /** @var App */
    private $app;

    /**
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        parent::__construct();
    }

    /**
     * @return App
     */
    public function getApp()
    {
        return $this->app;
    }

    /**
     * {@inheritDoc}
     */
    public function add(Command $command)
    {
        if ($command instanceof ContainerAwareInterface) {
            $command->setServiceContainer($this->app->getServiceContainer());
        }
        return parent::add($command);
    }
}
