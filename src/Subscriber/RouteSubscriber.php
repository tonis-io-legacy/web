<?php
namespace Tonis\Mvc;

use Tonis\Event\EventManager;
use Tonis\Event\SubscriberInterface;
use Tonis\Router\RouteCollection;

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
        $lifecycle->setRouteMatch($this->routes->match($lifecycle->getRequest()));
    }
}
