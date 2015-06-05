<?php
namespace Tonis\Mvc\Subscriber;

use Tonis\Event\EventManager;
use Tonis\Event\SubscriberInterface;
use Tonis\Mvc\LifecycleEvent;
use Tonis\Mvc\Tonis;
use Tonis\Router\RouteMatch;

final class RouteSubscriber implements SubscriberInterface
{
    /**
     * {@inheritDoc}
     */
    public function subscribe(EventManager $events)
    {
        $events->on(Tonis::EVENT_ROUTE, [$this, 'onRoute']);
    }

    /**
     * @param LifecycleEvent $lifecycle
     */
    public function onRoute(LifecycleEvent $lifecycle)
    {
        $routes = $lifecycle->getTonis()->routes();
        $match = $routes->match($lifecycle->getRequest());
        if ($match instanceof RouteMatch) {
            $lifecycle->setRouteMatch($match);
        }
    }
}
