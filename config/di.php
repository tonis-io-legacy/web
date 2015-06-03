<?php
use Tonis\Di;
use Tonis\Hookline;
use Tonis\Mvc\Factory;
use Tonis\View;

return function(Di\Container $di) {
    $di->set(Hookline\Container::class, Factory\HooklineContainerFactory::class);
    $di->set(View\Strategy\PlatesStrategy::class, Factory\PlatesStrategyFactory::class);
    $di->set(View\Strategy\TwigStrategy::class, Factory\TwigStrategyFactory::class);
    $di->set(View\Manager::class, Factory\ViewManagerFactory::class);
};
