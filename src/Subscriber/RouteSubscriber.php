<?php
namespace Tonis\Mvc\Subscriber;

use Tonis\Event\EventManager;
use Tonis\Event\SubscriberInterface;
use Tonis\Mvc\LifecycleEvent;
use Tonis\Mvc\Tonis;
use Tonis\Router\RouteCollection;
use Tonis\Router\RouteMatch;

final class RouteSubscriber implements SubscriberInterface
{
    /** @var Tonis */
    private $routes;

    /**
     * @param RouteCollection $routes
     */
    public function __construct(RouteCollection $routes)
    {
        $this->routes = $routes;
    }

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
        $match = $this->routes->match($lifecycle->getRequest());
        if ($match instanceof RouteMatch) {
            $lifecycle->setRouteMatch($match);
        }
    }
}
