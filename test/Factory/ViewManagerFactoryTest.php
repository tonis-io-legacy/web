<?php
namespace Tonis\Web\Factory;

use Tonis\Di\Container;
use Tonis\Web\TestAsset\TestPackage\TestPackage;
use Tonis\Package\PackageManager;
use Tonis\View\Strategy\StringStrategy;
use Tonis\View\ViewManager;

/**
 * @coversDefaultClass \Tonis\Web\Factory\ViewManagerFactory
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

        $di['config'] = [
            'tonis' => [
                'view_manager' => [
                    'fallback_strategy' => new StringStrategy(),
                    'error_template' => 'error',
                    'not_found_template' => '404'
                ]
            ]
        ];

        $factory = new ViewManagerFactory();
        $vm = $factory->__invoke($di);

        $this->assertInstanceOf(ViewManager::class, $vm);
        $this->assertSame('error', $vm->getErrorTemplate());
        $this->assertSame('404', $vm->getNotFoundTemplate());
        $this->assertEmpty($vm->getStrategies());
    }
}
