<?php
namespace Tonis\Mvc\Factory;

use Tonis\Di\Container;
use Tonis\Package\PackageManager;
use Tonis\View\Strategy\StringStrategy;
use Tonis\View\ViewManager;

/**
 * @coversDefaultClass \Tonis\Mvc\Factory\ViewManagerFactory
 */
class ViewStrategyFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::createService
     */
    public function testCreateService()
    {
        $di = new Container;
        $di->set(PackageManager::class, new PackageManager());
        $di['mvc'] = [
            'view_manager' => [
                'strategies' => [
                    StringStrategy::class,
                    'foo' => null
                ],
                'error_template' => '@error/error',
                'not_found_template' => '@error/404'
            ]
        ];

        $factory = new ViewManagerFactory();
        $vm = $factory->createService($di);

        $this->assertInstanceOf(ViewManager::class, $vm);
        $this->assertCount(1, $vm->getStrategies());
    }
}
