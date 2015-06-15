<?php
namespace Tonis\Web\Subscriber;

use League\Plates\Engine;
use Tonis\Di\Container;
use Tonis\Event\EventManager;
use Tonis\Web\Exception\InvalidDispatchResultException;
use Tonis\Web\Exception\InvalidTemplateException;
use Tonis\Web\LifecycleEvent;
use Tonis\Web\TestAsset\NewRequestTrait;
use Tonis\Web\TestAsset\TestAction;
use Tonis\Web\TestAsset\TestController;
use Tonis\Web\TestAsset\TestViewModelStrategy;
use Tonis\Web\App;
use Tonis\Router\Route;
use Tonis\Router\RouteMatch;
use Tonis\View\Model\StringModel;
use Tonis\View\Model\ViewModel;
use Tonis\View\Strategy\PlatesStrategy;
use Tonis\View\Strategy\StringStrategy;
use Tonis\View\ViewManager;

/**
 * @covers \Tonis\Web\Subscriber\WebSubscriber
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

    public function testSubscribe()
    {
        $events = new EventManager();
        $this->s->subscribe($events);

        $this->assertCount(5, $events->getListeners());
        $this->assertCount(1, $events->getListeners(App::EVENT_BOOTSTRAP));
        $this->assertCount(1, $events->getListeners(App::EVENT_ROUTE_ERROR));
        $this->assertCount(1, $events->getListeners(App::EVENT_DISPATCH));
        $this->assertCount(1, $events->getListeners(App::EVENT_DISPATCH_EXCEPTION));
        $this->assertCount(1, $events->getListeners(App::EVENT_RENDER_EXCEPTION));
    }

    public function testBootstrapViewManager()
    {
        $this->di->set(PlatesStrategy::class, new PlatesStrategy(new Engine), true);

        $this->s->bootstrapViewManager();

        $this->assertCount(3, $this->vm->getStrategies());
        $this->assertInstanceOf(StringStrategy::class, $this->vm->getStrategies()[1]);
        $this->assertInstanceOf(PlatesStrategy::class, $this->vm->getStrategies()[2]);
    }

    public function testOnDispatch()
    {
        $event = new LifecycleEvent($this->newRequest('/'));
        $event->setRouteMatch(new RouteMatch(new Route('/', TestAction::class)));

        $event->setDispatchResult(['foo' => 'bar']);
        $this->s->onDispatch($event);
        $model = $event->getDispatchResult();
        $this->assertInstanceOf(ViewModel::class, $model);
        $this->assertSame('@tonis/web/test-asset/test', $model->getTemplate());
        $this->assertSame(['foo' => 'bar'], $model->getVariables());

        $event->setDispatchResult('foo');
        $this->s->onDispatch($event);
        $this->assertInstanceOf(StringModel::class, $event->getDispatchResult());
        $this->assertSame('foo', $event->getDispatchResult()->getString());
    }

    public function testOnDispatchSetsTemplate()
    {
        $match = new RouteMatch(new Route('foo', [TestController::class, 'index']));
        $event = new LifecycleEvent($this->newRequest('/'));
        $event->setRouteMatch($match);
        $event->setDispatchResult(new ViewModel(null));
        $this->s->onDispatch($event);

        $model = $event->getDispatchResult();
        $this->assertSame('@tonis/web/test-asset/index', $model->getTemplate());

        $match = new RouteMatch(new Route('foo', new TestAction));
        $event = new LifecycleEvent($this->newRequest('/'));
        $event->setRouteMatch($match);
        $event->setDispatchResult(new ViewModel(null));
        $this->s->onDispatch($event);

        $model = $event->getDispatchResult();
        $this->assertSame('@tonis/web/test-asset/test', $model->getTemplate());
    }

    public function testOnDispatchWithNoViewModel()
    {
        $event = new LifecycleEvent($this->newRequest('/'));
        $event->setRouteMatch(new RouteMatch(new Route('/', function() {})));

        $event->setDispatchResult(['foo' => 'bar']);
        $this->s->onDispatch($event);
        $model = $event->getDispatchResult();

        $this->assertInstanceOf(ViewModel::class, $model);
        $this->assertSame('error/exception', $model->getTemplate());
        $this->assertEquals(
            ['type' => 'no-template-available', 'exception' => new InvalidTemplateException()],
            $model->getVariables()
        );
    }

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

    public function testOnRouteError()
    {
        $event = new LifecycleEvent($this->newRequest('/'));
        $this->s->onRouteError($event);

        $model = $event->getDispatchResult();
        $this->assertInstanceOf(ViewModel::class, $model);
        $this->assertSame('error/404', $model->getTemplate());
    }

    public function testOnRenderException()
    {
        $event = $this->getEvent();

        $this->s->onRenderException($event);
        $this->assertSame('error/exception:{"exception":null,"type":"exception","path":"\/"}', $event->getRenderResult());

        $event->setException(new InvalidDispatchResultException());
        $this->s->onRenderException($event);
        $this->assertSame('error/exception:{"exception":"Tonis\\\\Web\\\\Exception\\\\InvalidDispatchResultException","type":"invalid-dispatch-result","path":"\/"}', $event->getRenderResult());
    }

    protected function setUp()
    {
        $this->vm = new ViewManager(new StringStrategy());
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
