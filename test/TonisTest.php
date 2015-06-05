<?php
namespace Tonis\Mvc;

use Tonis\Di\Container;
use Tonis\Dispatcher\Dispatcher;
use Tonis\Event\EventManager;
use Tonis\Mvc\Subscriber\BootstrapSubscriber;
use Tonis\Mvc\Subscriber\DispatchSubscriber;
use Tonis\Mvc\Subscriber\RenderSubscriber;
use Tonis\Mvc\Subscriber\RouteSubscriber;
use Tonis\Mvc\TestAsset\NewRequestTrait;
use Tonis\Package\PackageManager;
use Tonis\Router\Route;
use Tonis\Router\RouteCollection;
use Tonis\Router\RouteMatch;
use Zend\Diactoros\Response;

/**
 * @coversDefaultClass \Tonis\Mvc\Tonis
 */
class TonisTest extends \PHPUnit_Framework_TestCase
{
    use NewRequestTrait;

    /** @var Tonis */
    private $tonis;

    /**
     * @covers ::createWithDefaults
     */
    public function testCreateWithDefaults()
    {
        $tonis = Tonis::createWithDefaults([]);
        $this->assertInstanceOf(Tonis::class, $tonis);

        $di = $tonis->di();
        $this->assertTrue($di->has(Dispatcher::class));
        $this->assertTrue($di->has(PackageManager::class));
        $this->assertTrue($di->has(BootstrapSubscriber::class));
        $this->assertTrue($di->has(DispatchSubscriber::class));
        $this->assertTrue($di->has(RenderSubscriber::class));
        $this->assertTrue($di->has(RouteSubscriber::class));
    }

    /**
     * @covers ::events
     */
    public function testEvents()
    {
        $this->assertInstanceOf(EventManager::class, $this->tonis->events());
    }

    /**
     * @covers ::__construct
     * @covers ::bootstrap
     */
    public function testBootstrap()
    {
        $bootstrap = false;
        $this->tonis->events()->on(Tonis::EVENT_BOOTSTRAP, function () use (&$bootstrap) {
            $bootstrap = true;
        });
        $this->tonis->bootstrap();

        $this->assertTrue($bootstrap);
    }

    /**
     * @covers ::dispatch
     */
    public function testDispatch()
    {
        $dispatch = false;
        $this->tonis->events()->on(Tonis::EVENT_DISPATCH, function () use (&$dispatch) {
            $dispatch = true;
        });

        $this->tonis->dispatch();
        $this->assertTrue($dispatch);
    }

    /**
     * @covers ::dispatch
     */
    public function testDispatchReturnsEarlyWithResponse()
    {
        $this->tonis->getLifecycleEvent()->setResponse(new Response);

        $dispatch = false;
        $this->tonis->events()->on(Tonis::EVENT_DISPATCH, function () use (&$dispatch) {
            $dispatch = true;
        });

        $this->tonis->dispatch();
        $this->assertFalse($dispatch);
    }

    /**
     * @covers ::dispatch
     */
    public function testDispatchCatchesExceptions()
    {
        $this->tonis->events()->on(Tonis::EVENT_DISPATCH, function () {
            throw new \RuntimeException();
        });

        $this->tonis->dispatch();

        $this->assertNotNull($this->tonis->getLifecycleEvent()->getException());
    }

    /**
     * @covers ::render
     */
    public function testRender()
    {
        $render = false;
        $this->tonis->events()->on(Tonis::EVENT_RENDER, function () use (&$render) {
            $render = true;
        });

        $this->tonis->render();

        $this->assertTrue($render);
    }

    /**
     * @covers ::render
     */
    public function testRenderReturnsEarlyWithResponse()
    {
        $this->tonis->getLifecycleEvent()->setResponse(new Response);

        $render = false;
        $this->tonis->events()->on(Tonis::EVENT_RENDER, function () use (&$render) {
            $render = true;
        });

        $this->tonis->render();
        $this->assertFalse($render);
    }

    /**
     * @covers ::render
     */
    public function testRenderCatchesExceptions()
    {
        $this->tonis->events()->on(Tonis::EVENT_RENDER, function () {
            throw new \RuntimeException();
        });

        $this->tonis->render();

        $this->assertNotNull($this->tonis->getLifecycleEvent()->getException());
    }

    /**
     * @covers ::__construct
     * @covers ::route
     */
    public function testRoute()
    {
        $error = false;
        $this->tonis->events()->on(Tonis::EVENT_ROUTE, function (LifecycleEvent $event) use (&$route) {
            $event->setRouteMatch(new RouteMatch(new Route('/', 'handler')));
        });
        $this->tonis->events()->on(Tonis::EVENT_ROUTE_ERROR, function () use (&$error) {
            $error = true;
        });

        $this->tonis->route($this->newRequest('/'));

        $this->assertFalse($error);
        $this->assertInstanceOf(RouteMatch::class, $this->tonis->getLifecycleEvent()->getRouteMatch());
    }

    /**
     * @covers ::route
     */
    public function testRouteReturnsEarlyWithResponse()
    {
        $this->tonis->getLifecycleEvent()->setResponse(new Response);

        $route = false;
        $this->tonis->events()->on(Tonis::EVENT_ROUTE, function () use (&$route) {
            $route = true;
        });

        $this->tonis->route();
        $this->assertFalse($route);
    }

    /**
     * @covers ::route
     */
    public function testRouteError()
    {
        $error = false;
        $this->tonis->events()->on(Tonis::EVENT_ROUTE_ERROR, function () use (&$error) {
            $error = true;
        });

        $this->tonis->route($this->newRequest('/'));

        $this->assertTrue($error);
    }

    /**
     * @covers ::isDebugEnabled
     */
    public function testIsDebugEnabled()
    {
        $this->assertFalse($this->tonis->isDebugEnabled());

        $tonis = new Tonis(['debug' => true]);
        $this->assertTrue($tonis->isDebugEnabled());
    }

    /**
     * @covers ::di
     */
    public function testDi()
    {
        $this->assertInstanceOf(Container::class, $this->tonis->di());
    }

    /**
     * @covers ::routes
     */
    public function testRoutes()
    {
        $this->assertInstanceOf(RouteCollection::class, $this->tonis->routes());
    }

    /**
     * @covers ::getLifecycleEvent
     */
    public function testGetLifecycleEvent()
    {
        $this->assertInstanceOf(LifecycleEvent::class, $this->tonis->getLifecycleEvent());
    }

    protected function setUp()
    {
        $this->tonis = new Tonis();
        $this->tonis->bootstrap();
    }
}
