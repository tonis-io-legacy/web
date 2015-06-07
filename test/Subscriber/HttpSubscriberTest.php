<?php
namespace Tonis\Mvc\Subscriber;

use Tonis\Di\Container;
use Tonis\Event\EventManager;
use Tonis\Mvc\Exception\InvalidDispatchResultException;
use Tonis\Mvc\Factory\TonisFactory;
use Tonis\Mvc\LifecycleEvent;
use Tonis\Mvc\TestAsset\NewRequestTrait;
use Tonis\Mvc\TestAsset\TestViewModelStrategy;
use Tonis\Mvc\Tonis;
use Tonis\Router\Route;
use Tonis\Router\RouteMatch;
use Tonis\View\Model\StringModel;
use Tonis\View\Model\ViewModel;
use Tonis\View\Strategy\StringStrategy;
use Tonis\View\ViewManager;

/**
 * @coversDefaultClass \Tonis\Mvc\Subscriber\HttpSubscriber
 */
class HttpSubscriberTest extends \PHPUnit_Framework_TestCase
{
    use NewRequestTrait;

    /** @var Tonis */
    private $tonis;
    /** @var HttpSubscriber */
    private $s;

    /**
     * @covers ::__construct
     * @covers ::subscribe
     */
    public function testSubscribe()
    {
        $events = new EventManager();
        $this->s->subscribe($events);

        $this->assertCount(3, $events->getListeners());
        $this->assertCount(1, $events->getListeners(Tonis::EVENT_ROUTE));
        $this->assertCount(1, $events->getListeners(Tonis::EVENT_DISPATCH));
        $this->assertCount(1, $events->getListeners(Tonis::EVENT_RENDER));
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
     * @covers ::createTemplateModel
     */
    public function testOnRenderCreatesTemplateIfMissing()
    {
        $event = new LifecycleEvent($this->newRequest('/'));
        $event->setDispatchResult(new ViewModel(null, ['foo' => 'bar']));

        $event->setRouteMatch(new RouteMatch(new Route('/', 'handler')));
        $this->s->onRender($event);
        $this->assertSame('handler', $event->getRenderResult());

        $event->setRenderResult(null);
        $event->setRouteMatch(new RouteMatch(new Route('/', [$this, 'foo'])));
        $this->s->onRender($event);
        $this->assertSame('tonis/mvc/subscriber/http-subscriber-test', $event->getRenderResult());

        $event->setRenderResult(null);
        $event->setRouteMatch(new RouteMatch(new Route('/', null)));
        $this->s->onRender($event);
        $this->assertSame('error/exception', $event->getRenderResult());
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
        $this->tonis = TonisFactory::fromDefaults();
        /** @var \Tonis\Di\Container $di */
        $di = $this->tonis->di();
        $di->wrap(ViewManager::class, function () {
            $vm = new ViewManager();
            $vm->addStrategy(new StringStrategy());
            $vm->addStrategy(new TestViewModelStrategy());

            return $vm;
        });

        $this->tonis->bootstrap();

        $this->s = new HttpSubscriber($this->tonis->di());
    }
}
