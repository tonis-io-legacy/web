<?php
namespace Tonis\Web\Subscriber;

use Tonis\Di\Container;
use Tonis\Event\EventManager;
use Tonis\View\Strategy\StringStrategy;
use Tonis\Web\LifecycleEvent;
use Tonis\Web\TestAsset\NewRequestTrait;
use Tonis\Web\App;
use Tonis\View\Model\JsonModel;
use Tonis\View\Model\StringModel;
use Tonis\View\Strategy\JsonStrategy;
use Tonis\View\ViewManager;

/**
 * @coversDefaultClass \Tonis\Web\Subscriber\ApiSubscriber
 */
class ApiSubscriberTest extends \PHPUnit_Framework_TestCase
{
    use NewRequestTrait;

    /** @var ApiSubscriber */
    private $s;
    /** @var Container */
    private $di;

    /**
     * @covers ::__construct
     * @covers ::subscribe
     */
    public function testSubscribe()
    {
        $events = new EventManager();
        $this->s->subscribe($events);

        $this->assertCount(5, $events->getListeners());
        $this->assertCount(1, $events->getListeners(App::EVENT_BOOTSTRAP));
        $this->assertCount(1, $events->getListeners(App::EVENT_ROUTE_ERROR));
        $this->assertCount(1, $events->getListeners(App::EVENT_DISPATCH));
        $this->assertCount(1, $events->getListeners(App::EVENT_DISPATCH_EXCEPTION));
        $this->assertCount(1, $events->getListeners(App::EVENT_RESPOND));
    }

    /**
     * @covers ::bootstrapViewManager
     */
    public function testBootstrapViewManager()
    {
        $vm = new ViewManager(new StringStrategy());
        $this->di->set(ViewManager::class, $vm, true);

        $this->s->bootstrapViewManager();

        $this->assertCount(1, $vm->getStrategies());
        $this->assertInstanceOf(JsonStrategy::class, $vm->getStrategies()[0]);
    }

    /**
     * @covers ::onDispatch
     */
    public function testOnDispatchHandlesArrayResults()
    {
        $event = new LifecycleEvent($this->newRequest('/'));
        $event->setDispatchResult(['foo' => 'bar']);

        $this->s->onDispatch($event);
        $this->assertInstanceOf(JsonModel::class, $event->getDispatchResult());
        $this->assertSame(['foo' => 'bar'], $event->getDispatchResult()->getData());
    }

    /**
     * @covers ::onDispatch
     */
    public function testOnDispatchHandlesStringResults()
    {
        $event = new LifecycleEvent($this->newRequest('/'));
        $event->setDispatchResult('foo');

        $this->s->onDispatch($event);
        $this->assertInstanceOf(StringModel::class, $event->getDispatchResult());
        $this->assertSame('foo', $event->getDispatchResult()->getString());
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
        $this->assertInstanceOf(JsonModel::class, $event->getDispatchResult());
        $this->assertSame($ex->getMessage(), $event->getDispatchResult()->getData()['message']);
        $this->assertSame(get_class($ex), $event->getDispatchResult()->getData()['exception']);
        $this->assertSame($ex->getTrace(), $event->getDispatchResult()->getData()['trace']);
    }

    /**
     * @covers ::onRouteError
     */
    public function testOnRouteError()
    {
        $event = new LifecycleEvent($this->newRequest('/'));
        $this->s->onRouteError($event);

        $this->assertInstanceOf(JsonModel::class, $event->getDispatchResult());
        $this->assertSame('Route could not be matched', $event->getDispatchResult()->getData()['error']);
        $this->assertSame('/', $event->getDispatchResult()->getData()['path']);
    }

    /**
     * @covers ::onRespond
     */
    public function testOnRespond()
    {
        $event = new LifecycleEvent($this->newRequest('/'));
        $this->s->onRespond($event);

        $this->assertSame(['application/json'], $event->getResponse()->getHeader('Content-Type'));
    }

    protected function setUp()
    {
        $this->di = new Container;
        $this->s = new ApiSubscriber($this->di);
    }
}
