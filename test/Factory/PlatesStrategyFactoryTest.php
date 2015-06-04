<?php
namespace Tonis\Mvc\Factory;

use League\Plates\Engine;
use Tonis\Di\Container;
use Tonis\Mvc\TestAsset\TestPackage\TestPackage;
use Tonis\Package\PackageManager;
use Tonis\View\Strategy\PlatesStrategy;

/**
 * @coversDefaultClass \Tonis\Mvc\Factory\PlatesStrategyFactory
 */
class PlatesStrategyFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::createService
     */
    public function testCreateService()
    {
        $pm = new PackageManager;
        $pm->add(TestPackage::class);
        $pm->load();

        $di = new Container;
        $di['mvc'] = [
            'plates' => [
                'folders' => [
                    'foo' => __DIR__
                ]
            ]
        ];
        $di->set(PackageManager::class, $pm);

        $factory = new PlatesStrategyFactory();

        $plates = $factory->createService($di);

        $this->assertInstanceOf(PlatesStrategy::class, $plates);
        $this->assertInstanceOf(Engine::class, $plates->getEngine());

        $folders = $plates->getEngine()->getFolders();
        $this->assertTrue($folders->exists('foo'));
        $this->assertTrue($folders->exists('test-package'));
    }
}
