<?php
namespace Tonis\Tonis\Factory;

use Tonis\Di\Container;
use Tonis\Tonis\TestAsset\TestPackage\TestPackage;
use Tonis\Tonis\TestAsset\TestViewStrategyFactory;
use Tonis\Package\PackageManager;

/**
 * @coversDefaultClass \Tonis\Tonis\Factory\AbstractViewStrategyFactory
 */
class AbstractViewStrategyFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::getViewPaths
     */
    public function testGetViewPath()
    {
        $pm = new PackageManager();
        $pm->add(TestPackage::class);
        $pm->load();

        $di = new Container;
        $di->set(PackageManager::class, $pm);

        $factory = new TestViewStrategyFactory();
        $paths = $factory->createService($di);

        $this->assertInternalType('array', $paths);
        $this->assertCount(1, $paths);
        $this->assertArrayHasKey('test-package', $paths);
    }
}
