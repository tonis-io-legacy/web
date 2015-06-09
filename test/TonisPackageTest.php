<?php
namespace Tonis\Mvc;

use Tonis\Di\Container;
use Tonis\Mvc\Factory\TonisFactory;
use Tonis\Mvc\TestAsset\TestSubscriber;
use Tonis\Package\PackageManager;
use Tonis\View\Strategy\PlatesStrategy;
use Tonis\View\Strategy\TwigStrategy;

/**
 * @coversDefaultClass \Tonis\Mvc\TonisPackage
 */
class TonisPackageTest extends \PHPUnit_Framework_TestCase
{
    /** @var TonisPackage */
    private $package;

    /**
     * @covers ::bootstrap
     */
    public function testBootstrap()
    {
        $tonis = (new TonisFactory)->createTonisInstance();
        $di = $tonis->di();
        $di['config'] = [
            'mvc' => [
                'subscribers' => [
                    new TestSubscriber(),
                    TestSubscriber::class => TestSubscriber::class
                ]
            ]
        ];

        $this->package->bootstrap($tonis);
        $this->assertNotEmpty($tonis->events()->getListeners());
        $this->assertCount(2, $tonis->events()->getListeners('foo'));
    }

    /**
     * @covers ::configureServices
     */
    public function testConfigureServices()
    {
        $pm = new PackageManager;

        $di = new Container;
        $di->set(PackageManager::class, $pm, true);

        $this->package->configureServices($di);

        $this->assertTrue($di->has(PlatesStrategy::class));
        $this->assertTrue($di->has(TwigStrategy::class));
        $this->assertSame($pm->getMergedConfig(), $di['config']);
    }

    protected function setUp()
    {
        $this->package = new TonisPackage();
    }
}
