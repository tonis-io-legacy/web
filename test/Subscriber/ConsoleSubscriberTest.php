<?php
namespace Tonis\Web\Subscriber;

use Tonis\Event\EventManager;
use Tonis\Web\AppFactory;
use Tonis\Web\TestAsset\TestPackage\TestPackage;
use Tonis\Web\App;
use Tonis\Package\PackageManager;

/**
 * @coversDefaultClass \Tonis\Web\Subscriber\ConsoleSubscriber
 */
class ConsoleSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__construct
     * @covers ::subscribe
     */
    public function testSubscribe()
    {
        $console = (new AppFactory)->createConsole(['packages' => [TestPackage::class]]);
        $console->getApp()->bootstrap();

        $subscriber = new ConsoleSubscriber($console->getApp()->getServiceContainer());

        $events = new EventManager;
        $subscriber->subscribe($events);

        $this->assertCount(1, $events->getListeners());
        $this->assertCount(1, $events->getListeners(App::EVENT_BOOTSTRAP));

        $events->fire(App::EVENT_BOOTSTRAP);
    }
}
