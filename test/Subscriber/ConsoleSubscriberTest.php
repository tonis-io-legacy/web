<?php
namespace Tonis\Mvc\Subscriber;

use Tonis\Event\EventManager;
use Tonis\Mvc\Factory\TonisFactory;
use Tonis\Mvc\TestAsset\TestPackage\TestPackage;
use Tonis\Mvc\Tonis;
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
        $console = (new TonisFactory)->createConsole(['packages' => [TestPackage::class]]);
        $console->getTonis()->bootstrap();

        $subscriber = new ConsoleSubscriber($console->getTonis()->di());

        $events = new EventManager;
        $subscriber->subscribe($events);

        $this->assertCount(1, $events->getListeners());
        $this->assertCount(1, $events->getListeners(Tonis::EVENT_BOOTSTRAP));

        $events->fire(Tonis::EVENT_BOOTSTRAP);
    }
}
