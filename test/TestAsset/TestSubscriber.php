<?php
namespace Tonis\Mvc\TestAsset;

use Tonis\Event\EventManager;
use Tonis\Event\SubscriberInterface;

class TestSubscriber implements SubscriberInterface
{
    /**
     * @param EventManager $events
     * @return void
     */
    public function subscribe(EventManager $events)
    {
        $events->on('foo', function() {
            return 'bar';
        });
    }
}
