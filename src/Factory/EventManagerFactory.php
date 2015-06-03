<?php
namespace Tonis\Mvc\Factory;

use Tonis\Di;
use Tonis\Event;

final class EventManagerFactory implements Di\ServiceFactoryInterface
{
    /** @var Event\SubscriberInterface[] */
    private $subscribers;

    /**
     * @param Event\SubscriberInterface[] $subscribers
     */
    public function __construct(array $subscribers = [])
    {
        $this->subscribers = $subscribers;
    }

    /**
     * @param Di\Container $di
     * @return Event\Manager
     */
    public function createService(Di\Container $di)
    {
        $events = new Event\Manager(Event\Manager::class);

        foreach ($this->subscribers as $subscriber) {
            $events->subscribe($subscriber);
        }

        return $events;
    }
}
