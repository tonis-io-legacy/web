<?php
namespace Tonis\Web\Package;

use Tonis\Di\Container;
use Tonis\Router\Router;
use Tonis\Web\AppFactory;
use Tonis\Web\TestAsset\PlainPackage;
use Tonis\Web\TestAsset\TestPackage\TestPackage;
use Tonis\Web\TestAsset\TestPackageWithNoConfigs;

/**
 * @coversDefaultClass \Tonis\Web\Package\AbstractPackage
 */
class AbstractPackageTest extends \PHPUnit_Framework_TestCase
{
    /** @var TestPackage */
    private $package;

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
        $this->assertSame('Tonis\Web\TestAsset\TestPackage', $this->package->getNamespace());
        $this->assertSame('Tonis\Web\TestAsset\TestPackage', $this->package->getNamespace());
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
        $factory = new AppFactory;
        $this->assertNull($this->package->bootstrap($factory->createWeb()));
    }

    /**
     * @covers ::bootstrapConsole
     */
    public function testBootstrapConsole()
    {
        $factory = new AppFactory;
        $this->assertNull($this->package->bootstrapConsole($factory->createConsole([])));
    }

    /**
     * @covers ::configureRoutes
     */
    public function testConfigureRoutes()
    {
        $package = new PlainPackage;
        $router = new Router;
        $package->configureRoutes($router);

        $this->assertEmpty($router->getRouteCollection());
    }

    /**
     * @covers ::configureServices
     */
    public function testConfigureServices()
    {
        $package = new PlainPackage;
        $di = new Container;
        $package->configureServices($di);
    }

    protected function setUp()
    {
        $this->package = new TestPackage();
    }
}
