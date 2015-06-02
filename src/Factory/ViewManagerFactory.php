<?php
namespace Tonis\Mvc\Factory;

use Tonis\Di\Container;
use Tonis\Di\ServiceFactoryInterface;
use Tonis\View\Manager;

class ViewManagerFactory implements ServiceFactoryInterface
{
    /**
     * @param Container $di
     * @return Manager
     */
    public function createService(Container $di)
    {
        $manager = new Manager();

        return $manager;
    }
}
