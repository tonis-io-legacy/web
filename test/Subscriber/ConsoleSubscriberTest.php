<?php
namespace Tonis\Mvc\Subscriber;

use Tonis\Event\EventManager;
use Tonis\Mvc\TestAsset\TestPackage\TestPackage;
use Tonis\Mvc\Tonis;
use Tonis\Mvc\TonisConsole;
use Tonis\Package\PackageManager;

/**
 * @coversDefaultClass \Tonis\Mvc\Subscriber\ConsoleSubscriber
 */
class ConsoleSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__construct
     * @covers ::subscribe
     */
    public function testSubscribe()
    {
        $pm = new PackageManager;
        $pm->add(TestPackage::class);
        $pm->load();

        $console = new TonisConsole;
        $subscriber = new ConsoleSubscriber($console, $pm);

        $events = new EventManager();
        $subscriber->subscribe($events);

        $this->assertCount(1, $events->getListeners());
        $this->assertCount(1, $events->getListeners(Tonis::EVENT_BOOTSTRAP));

        $events->fire(Tonis::EVENT_BOOTSTRAP);
    }
}
