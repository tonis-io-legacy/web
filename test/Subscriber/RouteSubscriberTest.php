<?php
namespace Tonis\Mvc\Subscriber;

use Tonis\Event\EventManager;
use Tonis\Mvc\LifecycleEvent;
use Tonis\Mvc\TestAsset\NewRequestTrait;
use Tonis\Mvc\Tonis;
use Tonis\Router\RouteCollection;
use Tonis\Router\RouteMatch;

/**
 * @coversDefaultClass \Tonis\Mvc\Subscriber\RouteSubscriber
 */
class RouteSubscriberTest extends \PHPUnit_Framework_TestCase
{
    use NewRequestTrait;

    /** @var RouteCollection */
    private $routes;
    /** @var RouteSubscriber */
    private $s;

    /**
     * @covers ::__construct
     * @covers ::subscribe
     */
    public function testSubscribe()
    {
        $events = new EventManager();
        $this->s->subscribe($events);

        $this->assertCount(1, $events->getListeners());
        $this->assertCount(1, $events->getListeners(Tonis::EVENT_ROUTE));
    }

    /**
     * @covers ::onRoute
     */
    public function testOnRoute()
    {
        $this->routes->get('/', 'foo');

        $event = new LifecycleEvent($this->newRequest('/asdf'));
        $this->s->onRoute($event);
        $this->assertNull($event->getRouteMatch());

        $event = new LifecycleEvent($this->newRequest('/'));
        $this->s->onRoute($event);
        $this->assertInstanceOf(RouteMatch::class, $event->getRouteMatch());
    }

    protected function setUp()
    {
        $this->routes = new RouteCollection;
        $this->s = new RouteSubscriber($this->routes);
    }
}
