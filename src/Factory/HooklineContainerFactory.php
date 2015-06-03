<?php
namespace Tonis\Mvc\Factory;

use Tonis\Di;
use Tonis\Hookline;
use Tonis\Mvc\Hook\TonisHookInterface;

final class HooklineContainerFactory implements Di\ServiceFactoryInterface
{
    /**
     * @param Di\Container $di
     * @return Hookline\Container
     */
    public function createService(Di\Container $di)
    {
        $container = new Hookline\Container(TonisHookInterface::class);
        $hooks = [];

        foreach ($hooks as $hook) {
            $container->add(Di\ContainerUtil::get($di, $hook));
        }

        return $container;
    }
}
