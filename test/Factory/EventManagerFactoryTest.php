<?php
namespace Tonis\Mvc\Factory;

use Tonis\Di\Container;
use Tonis\Event\EventManager;
use Tonis\Mvc\TestAsset\TestSubscriber;
use Tonis\Mvc\TonisConfig;

/**
 * @coversDefaultClass \Tonis\Mvc\Factory\EventManagerFactory
 */
class EventManagerFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__invoke
     */
    public function testInvoke()
    {
        $di = new Container;
        $di->set(TonisConfig::class, new TonisConfig(['subscribers' => [new TestSubscriber()]]));

        $factory = new EventManagerFactory();
        $events = $factory->__invoke($di);

        $this->assertInstanceOf(EventManager::class, $events);
        $this->assertCount(1, $events->getListeners('foo'));
        $this->assertCount(1, $events->getListeners());
    }
}
