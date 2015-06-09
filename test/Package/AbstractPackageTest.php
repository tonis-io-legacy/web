<?php
namespace Tonis\Mvc\Package;

use Tonis\Di\Container;
use Tonis\Mvc\Factory\TonisFactory;
use Tonis\Mvc\TestAsset\InvalidTestPackage\InvalidTestPackage;
use Tonis\Mvc\TestAsset\PlainPackage;
use Tonis\Mvc\TestAsset\TestPackage\TestPackage;
use Tonis\Mvc\TestAsset\TestPackageWithNoConfigs;
use Tonis\Router\RouteCollection;

/**
 * @coversDefaultClass \Tonis\Mvc\Package\AbstractPackage
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
        $factory = new TonisFactory;
        $this->assertNull($this->package->bootstrap($factory->createWeb()));
    }

    /**
     * @covers ::bootstrapConsole
     */
    public function testBootstrapConsole()
    {
        $factory = new TonisFactory;
        $this->assertNull($this->package->bootstrapConsole($factory->createConsole([])));
    }

    protected function setUp()
    {
        $this->package = new TestPackage();
    }
}
