<?php
namespace Tonis\Mvc\Factory;

use Tonis\Di\Container;
use Tonis\Di\ContainerUtil;
use Tonis\Event\EventManager;
use Tonis\Mvc\TonisConfig;

final class EventManagerFactory
{
    /**
     * @param Container $di
     * @return EventManager
     */
    public function __invoke(Container $di)
    {
        /** @var TonisConfig $config */
        $config = $di->get(TonisConfig::class);
        $events = new EventManager;

        foreach ($config->getSubscribers() as $subscriber => $factory) {
            if (is_int($subscriber)) {
                $subscriber = $factory;
            } else {
                $di->set($subscriber, $factory);
            }

            $events->subscribe(ContainerUtil::get($di, $subscriber));
        }

        return $events;
    }
}
