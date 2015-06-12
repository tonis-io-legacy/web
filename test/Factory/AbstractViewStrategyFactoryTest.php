<?php
namespace Tonis\Web\Factory;

use Tonis\Di\Container;
use Tonis\Web\TestAsset\TestPackage\TestPackage;
use Tonis\Web\TestAsset\TestViewStrategyFactory;
use Tonis\Package\PackageManager;

/**
 * @coversDefaultClass \Tonis\Web\Factory\AbstractViewStrategyFactory
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

        $services = new Container;
        $services->set(PackageManager::class, $pm);

        $factory = new TestViewStrategyFactory();
        $paths = $factory->createService($services);

        $this->assertInternalType('array', $paths);
        $this->assertCount(1, $paths);
        $this->assertArrayHasKey('test-package', $paths);
    }
}
