<?php
namespace Tonis\Tonis\Factory;

use Tonis\Dispatcher\Dispatcher;
use Tonis\Event\EventManager;
use Tonis\Tonis\Tonis;
use Tonis\Tonis\TonisConfig;
use Tonis\Tonis\TonisConsole;
use Tonis\Package\PackageManager;
use Tonis\Router\RouteCollection;
use Tonis\View\Strategy\PlatesStrategy;
use Tonis\View\Strategy\TwigStrategy;
use Tonis\View\ViewManager;

/**
 * @coversDefaultClass \Tonis\Tonis\Factory\TonisFactory
 */
class TonisFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::createTonisInstance
     * @covers ::prepareServices
     */
    public function testCreateTonisInstance()
    {
        $tonis = (new TonisFactory)->createTonisInstance();

        $this->assertInstanceOf(Tonis::class, $tonis);

        $di = $tonis->di();
        $this->assertTrue($di->has(TonisConfig::class));
        $this->assertTrue($di->has(PackageManager::class));
        $this->assertTrue($di->has(RouteCollection::class));
        $this->assertTrue($di->has(EventManager::class));
        $this->assertTrue($di->has(Dispatcher::class));
        $this->assertTrue($di->has(ViewManager::class));
    }

    /**
     * @covers ::createApi
     */
    public function testCreateApi()
    {
        $tonis = (new TonisFactory)->createApi();
        $this->assertInstanceOf(Tonis::class, $tonis);
        $this->assertNotEmpty($tonis->events()->getListeners());
    }

    /**
     * @covers ::createWeb
     */
    public function testCreateWeb()
    {
        $tonis = (new TonisFactory)->createWeb();
        $this->assertInstanceOf(Tonis::class, $tonis);
        $this->assertNotEmpty($tonis->events()->getListeners());
    }

    /**
     * @covers ::createConsole
     */
    public function testCreateConsole()
    {
        $console = (new TonisFactory)->createConsole();
        $this->assertInstanceOf(TonisConsole::class, $console);
        $this->assertInstanceOf(Tonis::class, $console->getTonis());
    }
}
