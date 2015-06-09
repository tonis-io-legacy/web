<?php
namespace Tonis\Mvc\Subscriber;

use League\Plates\Engine;
use Tonis\Di\Container;
use Tonis\Event\EventManager;
use Tonis\Mvc\Exception\InvalidDispatchResultException;
use Tonis\Mvc\LifecycleEvent;
use Tonis\Mvc\TestAsset\NewRequestTrait;
use Tonis\Mvc\TestAsset\TestViewModelStrategy;
use Tonis\Mvc\Tonis;
use Tonis\Router\Route;
use Tonis\Router\RouteMatch;
use Tonis\View\Model\StringModel;
use Tonis\View\Model\ViewModel;
use Tonis\View\Strategy\PlatesStrategy;
use Tonis\View\Strategy\StringStrategy;
use Tonis\View\ViewManager;

/**
 * @coversDefaultClass \Tonis\Mvc\Subscriber\WebSubscriber
 */
class WebSubscriberTest extends \PHPUnit_Framework_TestCase
{
    use NewRequestTrait;

    /** @var ViewManager */
    private $vm;
    /** @var WebSubscriber */
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
        $this->assertCount(1, $events->getListeners(Tonis::EVENT_BOOTSTRAP));
        $this->assertCount(1, $events->getListeners(Tonis::EVENT_ROUTE_ERROR));
        $this->assertCount(1, $events->getListeners(Tonis::EVENT_DISPATCH));
        $this->assertCount(1, $events->getListeners(Tonis::EVENT_DISPATCH_EXCEPTION));
        $this->assertCount(1, $events->getListeners(Tonis::EVENT_RENDER_EXCEPTION));
    }

    /**
     * @covers ::bootstrapViewManager
     */
    public function testBootstrapViewManager()
    {
        $this->di->set(PlatesStrategy::class, new PlatesStrategy(new Engine), true);

        $this->s->bootstrapViewManager();

        $this->assertCount(3, $this->vm->getStrategies());
        $this->assertInstanceOf(StringStrategy::class, $this->vm->getStrategies()[1]);
        $this->assertInstanceOf(PlatesStrategy::class, $this->vm->getStrategies()[2]);
    }

    /**
     * @covers ::onDispatch
     * @covers ::createTemplateModel
     */
    public function testOnDispatch()
    {
        $event = new LifecycleEvent($this->newRequest('/'));
        $event->setRouteMatch(new RouteMatch(new Route('/', 'handler')));

        $event->setDispatchResult(['foo' => 'bar']);
        $this->s->onDispatch($event);
        $this->assertInstanceOf(ViewModel::class, $event->getDispatchResult());
        $this->assertSame(['foo' => 'bar'], $event->getDispatchResult()->getVariables());

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
        $model = $event->getDispatchResult();
        $this->assertInstanceOf(ViewModel::class, $model);
        $this->assertSame('error/exception', $model->getTemplate());
    }

    /**
     * @covers ::onRouteError
     */
    public function testOnRouteError()
    {
        $event = new LifecycleEvent($this->newRequest('/'));
        $this->s->onRouteError($event);

        $model = $event->getDispatchResult();
        $this->assertInstanceOf(ViewModel::class, $model);
        $this->assertSame('error/404', $model->getTemplate());
    }

    /**
     * @covers ::onRenderException
     * @covers ::createExceptionModel
     */
    public function testOnRenderException()
    {
        $event = $this->getEvent();

        $this->s->onRenderException($event);
        $this->assertSame('error/exception:{"exception":null,"type":"exception","path":"\/"}', $event->getRenderResult());

        $event->setException(new InvalidDispatchResultException());
        $this->s->onRenderException($event);
        $this->assertSame('error/exception:{"exception":"Tonis\\\\Mvc\\\\Exception\\\\InvalidDispatchResultException","type":"invalid-dispatch-result","path":"\/"}', $event->getRenderResult());
    }

    protected function setUp()
    {
        $this->vm = new ViewManager;
        $this->vm->addStrategy(new TestViewModelStrategy());

        $this->di = new Container;
        $this->di->set(ViewManager::class, $this->vm, true);

        $this->s = new WebSubscriber($this->di);
    }

    protected function getEvent()
    {
        return new LifecycleEvent($this->newRequest('/'));
    }
}
