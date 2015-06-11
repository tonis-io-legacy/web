<?php
namespace Tonis\Tonis\Subscriber;

use Tonis\Event\EventManager;
use Tonis\Tonis\Factory\TonisFactory;
use Tonis\Tonis\LifecycleEvent;
use Tonis\Tonis\TestAsset\NewRequestTrait;
use Tonis\Tonis\TestAsset\TestSubscriber;
use Tonis\Tonis\TestAsset\TestViewModelStrategy;
use Tonis\Tonis\Tonis;
use Tonis\Router\Route;
use Tonis\Router\RouteMatch;
use Tonis\View\Model\StringModel;
use Tonis\View\Strategy\StringStrategy;
use Tonis\View\ViewManager;
use Zend\Diactoros\Response;

/**
 * @coversDefaultClass \Tonis\Tonis\Subscriber\BaseSubscriber
 */
class BaseSubscriberTest extends \PHPUnit_Framework_TestCase
{
    use NewRequestTrait;

    /** @var Tonis */
    private $tonis;
    /** @var BaseSubscriber */
    private $s;

    /**
     * @covers ::__construct
     * @covers ::subscribe
     */
    public function testSubscribe()
    {
        $events = new EventManager();
        $this->s->subscribe($events);

        $this->assertCount(6, $events->getListeners());
        $this->assertCount(1, $events->getListeners(Tonis::EVENT_ROUTE));
        $this->assertCount(1, $events->getListeners(Tonis::EVENT_ROUTE_ERROR));
        $this->assertCount(2, $events->getListeners(Tonis::EVENT_DISPATCH));
        $this->assertCount(1, $events->getListeners(Tonis::EVENT_DISPATCH_EXCEPTION));
        $this->assertCount(1, $events->getListeners(Tonis::EVENT_RENDER));
        $this->assertCount(1, $events->getListeners(Tonis::EVENT_RESPOND));
    }

    /**
     * @covers ::bootstrapPackageSubscribers
     */
    public function testBootstrapPackageSubscribers()
    {
        $di = $this->tonis->di();
        $di['config'] = ['tonis' => ['subscribers' => [new TestSubscriber()]]];

        $this->s->bootstrapPackageSubscribers();
        $this->assertNotEmpty($this->tonis->events()->getListeners());
    }

    /**
     * @covers ::onRender
     */
    public function testOnRenderWithModel()
    {
        $event = new LifecycleEvent($this->newRequest('/'));
        $event->setDispatchResult(new StringModel('testing'));

        $this->s->onRender($event);
        $this->assertSame('testing', $event->getRenderResult());
    }

    /**
     * @covers ::onRender
     */
    public function testOnRenderReturnsEarlyIfRenderResultIsNotNull()
    {
        $event = new LifecycleEvent($this->newRequest('/'));
        $event->setRenderResult('foo');

        $this->s->onRender($event);
        $this->assertSame('foo', $event->getRenderResult());
    }

    /**
     * @covers ::onRoute
     */
    public function testOnRoute()
    {
        $this->tonis->routes()->get('/', 'foo');

        $event = new LifecycleEvent($this->newRequest('/asdf'));
        $this->s->onRoute($event);
        $this->assertNull($event->getRouteMatch());

        $event = new LifecycleEvent($this->newRequest('/'));
        $this->s->onRoute($event);
        $this->assertInstanceOf(RouteMatch::class, $event->getRouteMatch());
    }

    /**
     * @covers ::onDispatch
     */
    public function testOnDispatchReturnsEarlyWithResult()
    {
        $event = new LifecycleEvent($this->newRequest('/'));
        $event->setDispatchResult('foo');
        $this->s->onDispatch($event);
        $this->assertSame('foo', $event->getDispatchResult());
    }

    /**
     * @covers ::onDispatch
     */
    public function testOnDispatchReturnsEarlyWithNoRouteMatch()
    {
        $event = new LifecycleEvent($this->newRequest('/'));
        $this->s->onDispatch($event);

        $this->assertNull($event->getDispatchResult());
    }

    /**
     * @covers ::onDispatch
     */
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

    /**
     * @covers ::onDispatch
     */
    public function testOnDispatchHandlesServiceDispatchables()
    {
        $handler = function () {
            return 'dispatched';
        };
        $this->tonis->di()->set('handler', $handler);

        $event = new LifecycleEvent($this->newRequest('/'));
        $event->setRouteMatch(new RouteMatch(new Route('/', 'handler')));

        $this->s->onDispatch($event);
        $this->assertSame('dispatched', $event->getDispatchResult());
    }

    /**
     * @covers ::onDispatchValidateResult
     * @expectedException \Tonis\Tonis\Exception\InvalidDispatchResultException
     */
    public function testOnDispatchValidateResult()
    {
        $event = new LifecycleEvent($this->newRequest('/'));
        $event->setDispatchResult(false);

        $this->s->onDispatchValidateResult($event);
    }

    /**
     * @covers ::onDispatchValidateResult
     */
    public function testOnDispatchValidateResultWithValidResult()
    {
        $event = new LifecycleEvent($this->newRequest('/'));
        $event->setDispatchResult(new StringModel('foo'));
        $this->s->onDispatchValidateResult($event);
    }

    /**
     * @covers ::onRespond
     */
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

    /**
     * @covers ::onDispatchException
     */
    public function testOnDispatchException()
    {
        $ex = new \RuntimeException('foo');

        $event = new LifecycleEvent($this->newRequest('/'));
        $event->setException($ex);

        $this->s->onDispatchException($event);
        $this->assertSame(500, $event->getResponse()->getStatusCode());
    }

    /**
     * @covers ::onRouteError
     */
    public function testOnRouteError()
    {
        $event = new LifecycleEvent($this->newRequest('/'));
        $this->s->onRouteError($event);
        $this->assertSame(404, $event->getResponse()->getStatusCode());
    }

    protected function setUp()
    {
        $this->tonis = (new TonisFactory)->createWeb();
        /** @var \Tonis\Di\Container $di */
        $di = $this->tonis->di();
        $di->wrap(ViewManager::class, function () {
            $vm = new ViewManager();
            $vm->addStrategy(new StringStrategy());
            $vm->addStrategy(new TestViewModelStrategy());

            return $vm;
        });

        $this->tonis->bootstrap();

        $this->s = new BaseSubscriber($this->tonis->di());
    }
}
