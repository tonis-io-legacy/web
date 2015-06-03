<?php
namespace Tonis\Mvc\Factory;

use Tonis\Di;
use Tonis\Event;
use Tonis\Mvc\TestAsset\TestSubscriber;

/**
 * @coversDefaultClass \Tonis\Mvc\Factory\EventManagerFactory
 */
class EventManagerFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__construct
     * @covers ::createService
     */
    public function testCreateService()
    {
        $di = new Di\Container;
        $factory = new EventManagerFactory([new TestSubscriber()]);
        $events = $factory->createService($di);

        $this->assertInstanceOf(Event\Manager::class, $events);
        $this->assertCount(1, $events->getListeners('foo'));
        $this->assertCount(1, $events->getListeners());
    }
}