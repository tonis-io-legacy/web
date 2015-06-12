<?php
namespace Tonis\Web;

use Tonis\Dispatcher\Dispatcher;
use Tonis\Event\EventManager;
use Tonis\Package\PackageManager;
use Tonis\Router\Router;
use Tonis\View\Strategy\PlatesStrategy;
use Tonis\View\Strategy\TwigStrategy;
use Tonis\View\ViewManager;

/**
 * @coversDefaultClass \Tonis\Web\AppFactory
 */
class AppFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::create
     * @covers ::prepareServices
     */
    public function testCreate()
    {
        $app = (new AppFactory)->create();

        $this->assertInstanceOf(App::class, $app);

        $services = $app->getServiceContainer();
        $this->assertTrue($services->has(AppConfig::class));
        $this->assertTrue($services->has(PackageManager::class));
        $this->assertTrue($services->has(Router::class));
        $this->assertTrue($services->has(EventManager::class));
        $this->assertTrue($services->has(Dispatcher::class));
        $this->assertTrue($services->has(ViewManager::class));
    }

    /**
     * @covers ::createApi
     */
    public function testCreateApi()
    {
        $app = (new AppFactory)->createApi();
        $this->assertInstanceOf(App::class, $app);
        $this->assertNotEmpty($app->getEventManager()->getListeners());
    }

    /**
     * @covers ::createWeb
     */
    public function testCreateWeb()
    {
        $app = (new AppFactory)->createWeb();
        $this->assertInstanceOf(App::class, $app);
        $this->assertNotEmpty($app->getEventManager()->getListeners());
    }

    /**
     * @covers ::createConsole
     */
    public function testCreateConsole()
    {
        $console = (new AppFactory)->createConsole();
        $this->assertInstanceOf(Console::class, $console);
        $this->assertInstanceOf(App::class, $console->getApp());
    }
}
