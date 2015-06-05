<?php
namespace Tonis\Mvc\Subscriber;

use Tonis\Event\EventManager;
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
 * @coversDefaultClass \Tonis\Mvc\Subscriber\RenderSubscriber
 */
class RenderSubscriberTest extends \PHPUnit_Framework_TestCase
{
    use NewRequestTrait;

    /** @var RenderSubscriber */
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

        $event->setRouteMatch(new RouteMatch(new Route('/', [$this, 'foo'])));
        $this->s->onRender($event);
        $this->assertSame('tonis/mvc/subscriber/render-subscriber-test', $event->getRenderResult());

        $event->setRouteMatch(new RouteMatch(new Route('/', null)));
        $this->s->onRender($event);
        $this->assertSame('error/exception', $event->getRenderResult());
    }

    protected function setUp()
    {
        $vm = new ViewManager;
        $vm->addStrategy(new StringStrategy);
        $vm->addStrategy(new TestViewModelStrategy());
        $this->s = new RenderSubscriber($vm);
    }
}
