<?php
namespace Tonis\Mvc\Package;

use Tonis\Di\Container;
use Tonis\Mvc\TestAsset\InvalidTestPackage\InvalidTestPackage;
use Tonis\Mvc\TestAsset\PlainPackage;
use Tonis\Mvc\TestAsset\TestPackage\TestPackage;
use Tonis\Mvc\TestAsset\TestPackageWithNoConfigs;
use Tonis\Mvc\Tonis;
use Tonis\Mvc\TonisConsole;
use Tonis\Router\Collection;

/**
 * @coversDefaultClass \Tonis\Mvc\Package\AbstractPackage
 */
class AbstractPackageTest extends \PHPUnit_Framework_TestCase
{
    /** @var TestPackage */
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
        $package = new InvalidTestPackage();
        $package->configureRoutes($routes);
    }

    /**
     * @covers ::configureDi
     */
    public function testConfigureDi()
    {
        $di = new Container();
        $this->package->configureDi($di);
        $this->assertSame('bar', $di->get('foo'));
    }

    /**
     * @covers ::configureDi
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Default MVC package expects di config to return a callable
     */
    public function testConfigureDiInvalidCallableThrowsException()
    {
        $di = new Container();
        $package = new InvalidTestPackage();
        $package->configureDi($di);
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
     * @covers ::getNamespace
     */
    public function testGetName()
    {
        $this->assertSame('Mvc\TestPackage', $this->package->getName());
        $this->assertSame('Mvc\TestPackage', $this->package->getName());
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
        $this->package = new TestPackage();
    }
}
