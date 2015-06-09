<?php
namespace Tonis\Mvc;

use Interop\Container\ContainerInterface;
use Tonis\Mvc\Factory\PlatesStrategyFactory;
use Tonis\Mvc\Factory\TwigStrategyFactory;
use Tonis\Mvc\Package\AbstractPackage;
use Tonis\View\Strategy\PlatesStrategy;
use Tonis\View\Strategy\TwigStrategy;

class Package extends AbstractPackage
{
    /**
     * {@inheritDoc}
     */
    public function configureServices(ContainerInterface $di)
    {
        if (!method_exists($di, 'set')) {
            return;
        }

        $di->set(PlatesStrategy::class, PlatesStrategyFactory::class);
        $di->set(TwigStrategy::class, TwigStrategyFactory::class);
    }
}
