<?php
namespace Tonis\Mvc\Subscriber;

use Tonis\Event\EventManager;
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
        $console = new TonisConsole;

        $di = $console->getTonis()->getDi();
        $di->set(PackageManager::class, new PackageManager());

        $subscriber = new ConsoleSubscriber($console);

        $events = new EventManager();
        $subscriber->subscribe($events);

        $this->assertCount(1, $events->getListeners());
        $this->assertCount(1, $events->getListeners('bootstrap'));

        $events->fire('bootstrap');
    }
}
