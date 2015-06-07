<?php
namespace Tonis\Mvc\Factory;

use Tonis\Di\Container;
use Tonis\Mvc\TestAsset\TestPackage\TestPackage;
use Tonis\Package\PackageManager;
use Tonis\View\Strategy\StringStrategy;
use Tonis\View\ViewManager;

/**
 * @coversDefaultClass \Tonis\Mvc\Factory\ViewManagerFactory
 */
class ViewStrategyFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__invoke
     */
    public function testInvoke()
    {
        $pm = new PackageManager;
        $pm->add(TestPackage::class);
        $pm->load();

        $di = new Container;
        $di->set(PackageManager::class, $pm);

        $factory = new ViewManagerFactory();
        $vm = $factory->__invoke($di);

        $this->assertInstanceOf(ViewManager::class, $vm);
        $this->assertCount(1, $vm->getStrategies());
    }
}
