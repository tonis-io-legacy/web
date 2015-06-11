<?php
namespace Tonis\Tonis\Factory;

use Tonis\Di\Container;
use Tonis\Tonis\TestAsset\TestPackage\TestPackage;
use Tonis\Tonis\TestAsset\TestTwigExtension;
use Tonis\Package\PackageManager;
use Tonis\View\Strategy\TwigStrategy;

/**
 * @coversDefaultClass \Tonis\Tonis\Factory\TwigStrategyFactory
 */
class TwigStrategyFactoryTest extends \PHPUnit_Framework_TestCase
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
        $di['config'] = [
            'twig' => [
                'extensions' => [
                    TestTwigExtension::class
                ],
                'namespaces' => [
                    'foo' => __DIR__ . '/../TestAsset/TestPackage'
                ],
                'options' => []
            ]
        ];
        $di->set(PackageManager::class, $pm);

        $factory = new TwigStrategyFactory();

        $twig = $factory->__invoke($di);

        $this->assertInstanceOf(TwigStrategy::class, $twig);
        $this->assertInstanceOf(\Twig_Environment::class, $twig->getTwig());
        $this->assertInstanceOf(TestTwigExtension::class, $twig->getTwig()->getExtension('test'));
    }
}
