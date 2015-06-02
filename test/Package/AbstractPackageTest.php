<?php
namespace Tonis\Mvc\Package;

use Tonis\Di\Container;
use Tonis\Mvc\Package;
use Tonis\Mvc\TestAsset\TestPackageWithInvalidConfigs;
use Tonis\Mvc\TestAsset\TestPackageWithNoConfigs;
use Tonis\Mvc\Tonis;
use Tonis\Mvc\TonisConsole;
use Tonis\Router\Collection;
use Tonis\View\Strategy\PlatesStrategy;

/**
 * @coversDefaultClass \Tonis\Mvc\Package\AbstractPackage
 */
class AbstractPackageTest extends \PHPUnit_Framework_TestCase
{
    /** @var Package */
    private $package;

    /**
     * @covers ::configureRoutes
     */
    public function testConfigureRoutes()
    {
        $routes = new Collection();
        $this->package->configureRoutes($routes);
        $this->assertCount(1, $routes->getRoutes());
    }

    /**
     * @covers ::configureRoutes
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Default MVC package expects routes config to return a callable
     */
    public function testConfigureRoutesInvalidCallableThrowsException()
    {
        $routes = new Collection();
        $package = new TestPackageWithInvalidConfigs();
        $package->configureRoutes($routes);
    }

    /**
     * @covers ::configureDi
     */
    public function testConfigureDi()
    {
        $di = new Container();
        $di['tonis'] = ['plates' => ['folders' => []]];
        $di->set(Tonis::class, new Tonis());
        $this->package->configureDi($di);
        $this->assertInstanceOf(PlatesStrategy::class, $di->get(PlatesStrategy::class));
    }

    /**
     * @covers ::configureDi
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Default MVC package expects di config to return a callable
     */
    public function testConfigureDiInvalidCallableThrowsException()
    {
        $di = new Container();
        $package = new TestPackageWithInvalidConfigs();
        $package->configureDi($di);
    }

    /**
     * @covers ::getConfig
     */
    public function testGetConfig()
    {
        $package = new TestPackageWithNoConfigs();
        $this->assertSame([], $package->getConfig());

        $package = new Package();
        $this->assertSame(include __DIR__ . '/../../config/package.php', $package->getConfig());
    }

    /**
     * @covers ::getPath
     */
    public function testGetPath()
    {
        $package = new Package();
        $this->assertSame(realpath(__DIR__ . '/../../'), $package->getPath());
        $this->assertSame(realpath(__DIR__ . '/../../'), $package->getPath());
    }

    /**
     * @covers ::bootstrap
     */
    public function testBootstrap()
    {
        $this->assertNull($this->package->bootstrap(new Tonis()));
    }

    /**
     * @covers ::bootstrapConsole
     */
    public function testBootstrapConsole()
    {
        $this->assertNull($this->package->bootstrapConsole(new TonisConsole()));
    }

    protected function setUp()
    {
        $this->package = new Package();
    }
}
