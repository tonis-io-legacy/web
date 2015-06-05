<?php
namespace Tonis\Mvc\Factory;

use Tonis\Di\Container;
use Tonis\Di\ContainerUtil;
use Tonis\Di\ServiceFactoryInterface;
use Tonis\Event\EventManager;
use Tonis\Event\SubscriberInterface;

final class EventManagerFactory implements ServiceFactoryInterface
{
    /** @var SubscriberInterface[] */
    private $subscribers;

    /**
     * @param SubscriberInterface[] $subscribers
     */
    public function __construct(array $subscribers = [])
    {
        $this->subscribers = $subscribers;
    }

    /**
     * @param Container $di
     * @return EventManager
     */
    public function createService(Container $di)
    {
        $events = new EventManager(EventManager::class);

        foreach ($this->subscribers as $subscriber) {
            $events->subscribe(ContainerUtil::get($di, $subscriber));
        }

        return $events;
    }
}
