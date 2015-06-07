<?php
namespace Tonis\Mvc\Subscriber;

use Tonis\Event\EventManager;
use Tonis\Mvc\Factory\TonisFactory;
use Tonis\Mvc\LifecycleEvent;
use Tonis\Mvc\TestAsset\NewRequestTrait;
use Tonis\Mvc\Tonis;

/**
 * @coversDefaultClass \Tonis\Mvc\Subscriber\BootstrapSubscriber
 */
class BootstrapSubscriberTest extends \PHPUnit_Framework_TestCase
{
    use NewRequestTrait;

    /** @var Tonis */
    private $tonis;
    /** @var BootstrapSubscriber */
    private $s;

    /**
     * @covers ::subscribe
     */
    public function testSubscribe()
    {
        $events = new EventManager();
        $this->s->subscribe($events);

        $this->assertCount(1, $events->getListeners());
        $this->assertCount(1, $events->getListeners(Tonis::EVENT_BOOTSTRAP));
    }

    public function testOnBootstrap()
    {
        $event = new LifecycleEvent($this->newRequest('/'));
        $this->s->bootstrapPackageManager($event);
    }

    protected function setUp()
    {
        $this->tonis = TonisFactory::fromDefaults();
        $this->s = new BootstrapSubscriber($this->tonis->di());
    }
}
