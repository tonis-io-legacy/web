<?php
namespace Tonis\Mvc\Package;

use Tonis\Di\Container;
use Tonis\Mvc\Factory\TonisFactory;
use Tonis\Mvc\TestAsset\InvalidTestPackage\InvalidTestPackage;
use Tonis\Mvc\TestAsset\PlainPackage;
use Tonis\Mvc\TestAsset\TestPackage\TestPackage;
use Tonis\Mvc\TestAsset\TestPackageWithNoConfigs;
use Tonis\Mvc\Tonis;
use Tonis\Mvc\TonisConsole;
use Tonis\Router\RouteCollection;

/**
 * @coversDefaultClass \Tonis\Mvc\Package\AbstractPackage
 */
class AbstractPackageTest extends \PHPUnit_Framework_TestCase
{
    /** @var TestPackage */
    private $package;

    /**
     * @covers ::configureRoutes
     * @covers ::loadCallable
     */
    public function testConfigureRoutes()
    {
        $routes = new RouteCollection();
        $this->package->configureRoutes($routes);
        $this->assertCount(1, $routes->getRoutes());
    }

    /**
     * @covers ::configureRoutes
     * @covers ::loadCallable
     * @expectedException \RuntimeException
     * @expectedExceptionMessage routes.php should return a callable
     */
    public function testConfigureRoutesInvalidCallableThrowsException()
    {
        $routes = new RouteCollection();
        $package = new InvalidTestPackage();
        $package->configureRoutes($routes);
    }

    /**
     * @covers ::configureServices
     * @covers ::loadCallable
     */
    public function testConfigureServices()
    {
        $di = new Container();
        $this->package->configureServices($di);
        $this->assertSame('bar', $di->get('foo'));
    }

    /**
     * @covers ::configureServices
     * @covers ::loadCallable
     * @expectedException \RuntimeException
     * @expectedExceptionMessage services.php should return a callable
     */
    public function testConfigureDiInvalidCallableThrowsException()
    {
        $di = new Container();
        $package = new InvalidTestPackage();
        $package->configureServices($di);
    }

    /**
     * @covers ::getConfig
     */
    public function testGetConfig()
    {
        $package = new TestPackageWithNoConfigs();
        $this->assertSame([], $package->getConfig());

        $package = new TestPackage();
        $this->assertSame(include __DIR__ . '/../TestAsset/TestPackage/config/package.php', $package->getConfig());
    }

    /**
     * @covers ::getPath
     */
    public function testGetPath()
    {
        $package = new PlainPackage();
        $this->assertSame(realpath(__DIR__ . '/../'), $package->getPath());
        $this->assertSame(realpath(__DIR__ . '/../'), $package->getPath());
    }

    /**
     * @covers ::getNamespace
     */
    public function testGetNamespace()
    {
        $this->assertSame('Tonis\Mvc\TestAsset\TestPackage', $this->package->getNamespace());
        $this->assertSame('Tonis\Mvc\TestAsset\TestPackage', $this->package->getNamespace());
    }

    /**
     * @covers ::getName
     */
    public function testGetName()
    {
        $this->assertSame('test-package', $this->package->getName());
        $this->assertSame('test-package', $this->package->getName());
    }

    /**
     * @covers ::bootstrap
     */
    public function testBootstrap()
    {
        $this->assertNull($this->package->bootstrap(TonisFactory::fromDefaults()));
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
        $this->package = new TestPackage();
    }
}
