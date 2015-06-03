<?php
namespace Tonis\Mvc\Factory;

use Tonis\Di;
use Tonis\Hookline;
use Tonis\Mvc\Hook\TonisHookInterface;

final class HooklineContainerFactory implements Di\ServiceFactoryInterface
{
    /** @var array */
    private $hooks;

    /**
     * @param array $hooks
     */
    public function __construct(array $hooks)
    {
        $this->hooks = $hooks;
    }

    /**
     * @param Di\Container $di
     * @return Hookline\Container
     */
    public function createService(Di\Container $di)
    {
        $container = new Hookline\Container(TonisHookInterface::class);

        foreach ($this->hooks as $hook) {
            $container->add(Di\ContainerUtil::get($di, $hook));
        }

        return $container;
    }
}
