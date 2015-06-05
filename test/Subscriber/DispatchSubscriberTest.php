<?php
namespace Tonis\Mvc\Subscriber;

use Tonis\Dispatcher\Dispatcher;
use Tonis\Event\EventManager;
use Tonis\Mvc\Exception\InvalidDispatchResultException;
use Tonis\Mvc\LifecycleEvent;
use Tonis\Mvc\TestAsset\NewRequestTrait;
use Tonis\Mvc\Tonis;
use Tonis\Router\Route;
use Tonis\Router\RouteMatch;
use Tonis\View\Model\StringModel;
use Tonis\View\Model\ViewModel;

/**
 * @coversDefaultClass \Tonis\Mvc\Subscriber\DispatchSubscriber
 */
class DispatchSubscriberTest extends \PHPUnit_Framework_TestCase
{
    use NewRequestTrait;

    /** @var DispatchSubscriber */
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
        $this->assertCount(1, $events->getListeners(Tonis::EVENT_DISPATCH));
    }

    /**
     * @covers ::onDispatch
     */
    public function testOnDispatchConvertsStringToStringModel()
    {
        $event = new LifecycleEvent($this->newRequest('/'));
        $event->setRouteMatch(new RouteMatch(new Route('/', 'handler')));

        $this->s->onDispatch($event);

        $model = $event->getDispatchResult();
        $this->assertInstanceOf(StringModel::class, $model);
        $this->assertSame('handler', $model->getString());
    }

    /**
     * @covers ::onDispatch
     */
    public function testOnDispatchConvertsArrayToViewModel()
    {
        $event = new LifecycleEvent($this->newRequest('/'));
        $event->setRouteMatch(new RouteMatch(new Route('/', ['foo' => 'bar'])));

        $this->s->onDispatch($event);

        $model = $event->getDispatchResult();
        $this->assertInstanceOf(ViewModel::class, $model);
        $this->assertSame(['foo' => 'bar'], $model->getVariables());
        $this->assertNull($model->getTemplate());
    }

    /**
     * @covers ::onDispatch
     */
    public function testOnDispatchHandlesInvalidResults()
    {
        $event = new LifecycleEvent($this->newRequest('/'));
        $event->setRouteMatch(new RouteMatch(new Route('/', null)));

        $this->s->onDispatch($event);

        $this->assertNull($event->getDispatchResult());
        $this->assertInstanceOf(InvalidDispatchResultException::class, $event->getException());
    }

    /**
     * @covers ::onDispatch
     */
    public function testOnDispatchReturnsEarlyWithResult()
    {
        $event = new LifecycleEvent($this->newRequest('/'));
        $event->setDispatchResult('foo');
        $this->s->onDispatch($event);
    }

    protected function setUp()
    {
        $this->s = new DispatchSubscriber(new Dispatcher);
    }
}
