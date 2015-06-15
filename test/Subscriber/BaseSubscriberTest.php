<?php
namespace Tonis\Web\Subscriber;

use Tonis\Event\EventManager;
use Tonis\Web\AppFactory;
use Tonis\Web\LifecycleEvent;
use Tonis\Web\TestAsset\NewRequestTrait;
use Tonis\Web\TestAsset\TestController;
use Tonis\Web\TestAsset\TestSubscriber;
use Tonis\Web\TestAsset\TestViewModelStrategy;
use Tonis\Web\App;
use Tonis\Router\Route;
use Tonis\Router\RouteMatch;
use Tonis\View\Model\StringModel;
use Tonis\View\Strategy\StringStrategy;
use Tonis\View\ViewManager;
use Zend\Diactoros\Response;

/**
 * @covers \Tonis\Web\Subscriber\BaseSubscriber
 */
class BaseSubscriberTest extends \PHPUnit_Framework_TestCase
{
    use NewRequestTrait;

    /** @var App */
    private $app;
    /** @var BaseSubscriber */
    private $s;

    public function testSubscribe()
    {
        $events = new EventManager();
        $this->s->subscribe($events);

        $this->assertCount(6, $events->getListeners());
        $this->assertCount(1, $events->getListeners(App::EVENT_ROUTE));
        $this->assertCount(1, $events->getListeners(App::EVENT_ROUTE_ERROR));
        $this->assertCount(2, $events->getListeners(App::EVENT_DISPATCH));
        $this->assertCount(1, $events->getListeners(App::EVENT_DISPATCH_EXCEPTION));
        $this->assertCount(1, $events->getListeners(App::EVENT_RENDER));
        $this->assertCount(1, $events->getListeners(App::EVENT_RESPOND));
    }

    public function testBootstrapPackageSubscribers()
    {
        $di = $this->app->getServiceContainer();
        $di['config'] = ['tonis' => ['subscribers' => [new TestSubscriber()]]];

        $this->s->bootstrapPackageSubscribers();
        $this->assertNotEmpty($this->app->getEventManager()->getListeners());
    }

    public function testOnRenderWithModel()
    {
        $event = new LifecycleEvent($this->newRequest('/'));
        $event->setDispatchResult(new StringModel('testing'));

        $this->s->onRender($event);
        $this->assertSame('testing', $event->getRenderResult());
    }

    public function testOnRenderReturnsEarlyIfRenderResultIsNotNull()
    {
        $event = new LifecycleEvent($this->newRequest('/'));
        $event->setRenderResult('foo');

        $this->s->onRender($event);
        $this->assertSame('foo', $event->getRenderResult());
    }

    public function testOnRoute()
    {
        $this->app->getRouter()->get('/', 'foo');

        $event = new LifecycleEvent($this->newRequest('/asdf'));
        $this->s->onRoute($event);
        $this->assertNull($event->getRouteMatch());

        $event = new LifecycleEvent($this->newRequest('/'));
        $this->s->onRoute($event);
        $this->assertInstanceOf(RouteMatch::class, $event->getRouteMatch());
    }

    public function testOnDispatchReturnsEarlyWithResult()
    {
        $event = new LifecycleEvent($this->newRequest('/'));
        $event->setDispatchResult('foo');
        $this->s->onDispatch($event);
        $this->assertSame('foo', $event->getDispatchResult());
    }

    public function testOnDispatchReturnsEarlyWithNoRouteMatch()
    {
        $event = new LifecycleEvent($this->newRequest('/'));
        $this->s->onDispatch($event);

        $this->assertNull($event->getDispatchResult());
    }

    public function testOnDispatch()
    {
        $handler = function () {
            return 'dispatched';
        };

        $event = new LifecycleEvent($this->newRequest('/'));
        $event->setRouteMatch(new RouteMatch(new Route('/', $handler)));

        $this->s->onDispatch($event);
        $this->assertSame('dispatched', $event->getDispatchResult());
    }

    public function testOnDispatchHandlesServiceDispatchables()
    {
        $handler = function () {
            return 'dispatched';
        };
        $this->app->getServiceContainer()->set('handler', $handler);

        $event = new LifecycleEvent($this->newRequest('/'));
        $event->setRouteMatch(new RouteMatch(new Route('/', 'handler')));

        $this->s->onDispatch($event);
        $this->assertSame('dispatched', $event->getDispatchResult());
    }

    public function testOnDispatchHandlesCallableServices()
    {
        $this->app->getServiceContainer()->set('handler', new TestController(), true);

        $event = new LifecycleEvent($this->newRequest('/'));
        $event->setRouteMatch(new RouteMatch(new Route('/', ['handler', 'index'])));

        $this->s->onDispatch($event);
        $this->assertTrue($event->getDispatchResult());
    }

    /**
     * @expectedException \Tonis\Web\Exception\InvalidDispatchResultException
     */
    public function testOnDispatchValidateResult()
    {
        $event = new LifecycleEvent($this->newRequest('/'));
        $event->setDispatchResult(false);

        $this->s->onDispatchValidateResult($event);
    }

    public function testOnDispatchValidateResultWithValidResult()
    {
        $event = new LifecycleEvent($this->newRequest('/'));
        $event->setDispatchResult(new StringModel('foo'));
        $this->s->onDispatchValidateResult($event);
    }

    public function testOnRespond()
    {
        $response = new Response;

        $event = new LifecycleEvent($this->newRequest('/'));
        $event->setResponse($response);
        $event->setRenderResult('response');

        $this->s->onRespond($event);
        $this->assertSame($response, $event->getResponse());
        $this->assertSame('response', (string) $response->getBody());

        $event = new LifecycleEvent($this->newRequest('/'));
        $this->s->onRespond($event);
        $this->assertNotSame($response, $event->getResponse());
        $this->assertInstanceOf(Response::class, $event->getResponse());
        $this->assertSame('response', (string) $response->getBody());
    }

    public function testOnDispatchException()
    {
        $ex = new \RuntimeException('foo');

        $event = new LifecycleEvent($this->newRequest('/'));
        $event->setException($ex);

        $this->s->onDispatchException($event);
        $this->assertSame(500, $event->getResponse()->getStatusCode());
    }

    public function testOnRouteError()
    {
        $event = new LifecycleEvent($this->newRequest('/'));
        $this->s->onRouteError($event);
        $this->assertSame(404, $event->getResponse()->getStatusCode());
    }

    protected function setUp()
    {
        $this->app = (new AppFactory)->createWeb();
        /** @var \Tonis\Di\Container $di */
        $services = $this->app->getServiceContainer();
        $services->wrap(ViewManager::class, function () {
            $vm = new ViewManager(new StringStrategy());
            $vm->addStrategy(new TestViewModelStrategy());

            return $vm;
        });

        $this->app->bootstrap();

        $this->s = new BaseSubscriber($this->app->getServiceContainer());
    }
}
