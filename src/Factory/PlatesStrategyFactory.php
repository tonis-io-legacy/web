<?php
namespace Tonis\Mvc\Factory;

use Tonis\Di;
use Tonis\View;

final class PlatesStrategyFactory implements Di\ServiceFactoryInterface
{
    /**
     * @param Di\Container $di
     * @return View\Strategy\PlatesStrategy
     */
    public function createService(Di\Container $di)
    {
        $strategy = new View\Strategy\PlatesStrategy($di->get())
    }
}
