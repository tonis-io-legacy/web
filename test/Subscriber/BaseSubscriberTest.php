<?php
namespace Tonis\Mvc\Subscriber;

use Tonis\Event\EventManager;
use Tonis\Mvc\Factory\TonisFactory;
use Tonis\Mvc\LifecycleEvent;
use Tonis\Mvc\TestAsset\NewRequestTrait;
use Tonis\Mvc\Tonis;

/**
 * @coversDefaultClass \Tonis\Mvc\Subscriber\BaseSubscriber
 */
class BaseSubscriberTest extends \PHPUnit_Framework_TestCase
{
    use NewRequestTrait;

    /** @var Tonis */
    private $tonis;
    /** @var BaseSubscriber */
    private $s;

    /**
     * @covers ::subscribe
     */
    public function testSubscribe()
    {
        $events = new EventManager();
        $this->s->subscribe($events);

        $this->assertCount(4, $events->getListeners());
        $this->assertCount(1, $events->getListeners(Tonis::EVENT_ROUTE));
        $this->assertCount(2, $events->getListeners(Tonis::EVENT_DISPATCH));
        $this->assertCount(1, $events->getListeners(Tonis::EVENT_RENDER));
        $this->assertCount(1, $events->getListeners(Tonis::EVENT_RESPOND));
    }

    protected function setUp()
    {
        $this->tonis = (new TonisFactory)->createWeb();
        $this->s = new BaseSubscriber($this->tonis->di());
    }
}
