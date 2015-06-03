<?php
namespace Tonis\Mvc\TestAsset;

use Tonis\Event\Manager;
use Tonis\Event\SubscriberInterface;

class TestSubscriber implements SubscriberInterface
{
    /**
     * @param Manager $events
     * @return void
     */
    public function subscribe(Manager $events)
    {
        $events->on('foo', function() {
            return 'bar';
        });
    }
}
