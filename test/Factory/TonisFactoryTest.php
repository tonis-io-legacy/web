<?php
namespace Tonis\Mvc\Factory;

use Tonis\Dispatcher\Dispatcher;
use Tonis\Event\EventManager;
use Tonis\Mvc\Tonis;
use Tonis\Mvc\TonisConfig;
use Tonis\Package\PackageManager;
use Tonis\Router\RouteCollection;
use Tonis\View\Strategy\PlatesStrategy;
use Tonis\View\Strategy\TwigStrategy;
use Tonis\View\ViewManager;

/**
 * @coversDefaultClass \Tonis\Mvc\Factory\TonisFactory
 */
class TonisFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::fromDefaults
     */
    public function testInvoke()
    {
        $tonis = TonisFactory::fromDefaults();

        $this->assertInstanceOf(Tonis::class, $tonis);

        $di = $tonis->di();
        $this->assertTrue($di->has(TonisConfig::class));
        $this->assertTrue($di->has(PackageManager::class));
        $this->assertTrue($di->has(RouteCollection::class));
        $this->assertTrue($di->has(EventManager::class));
        $this->assertTrue($di->has(Dispatcher::class));
        $this->assertTrue($di->has(ViewManager::class));
        $this->assertTrue($di->has(PlatesStrategy::class));
        $this->assertTrue($di->has(TwigStrategy::class));
    }
}
