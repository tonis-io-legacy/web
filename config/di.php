<?php
use Tonis\Di;
use Tonis\Mvc\Factory;
use Tonis\View;

return function(Di\Container $di) {
    $di->set(View\Strategy\PlatesStrategy::class, Factory\PlatesStrategyFactory::class);
    $di->set(View\Strategy\TwigStrategy::class, Factory\TwigStrategyFactory::class);
};
