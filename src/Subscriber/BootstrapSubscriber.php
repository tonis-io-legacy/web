<?php
namespace Tonis\Mvc\Subscriber;

use Tonis\Event\EventManager;
use Tonis\Event\SubscriberInterface;
use Tonis\Mvc\LifecycleEvent;
use Tonis\Mvc\Tonis;

final class BootstrapSubscriber implements SubscriberInterface
{
    /**
     * {@inheritDoc}
     */
    public function subscribe(EventManager $events)
    {
        $events->on(Tonis::EVENT_BOOTSTRAP, [$this, 'bootstrapPackageManager']);
    }

    public function bootstrapPackageManager(LifecycleEvent $lifecycle)
    {
    }
}
