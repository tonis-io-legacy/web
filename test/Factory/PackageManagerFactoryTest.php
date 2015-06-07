<?php
namespace Tonis\Mvc\Factory;

use Tonis\Di\Container;
use Tonis\Mvc\TestAsset\TestPackage\TestPackage;
use Tonis\Mvc\TonisConfig;
use Tonis\Package\PackageManager;

/**
 * @coversDefaultClass \Tonis\Mvc\Factory\PackageManagerFactory
 */
class PackageManagerFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__invoke
     */
    public function testInvoke()
    {
        $di = new Container;
        $di->set(TonisConfig::class, new TonisConfig(['packages' => [TestPackage::class]]));

        $factory = new PackageManagerFactory();
        $pm = $factory->__invoke($di);

        $this->assertInstanceOf(PackageManager::class, $pm);
        $this->assertCount(2, $pm->getPackages());
    }
}
