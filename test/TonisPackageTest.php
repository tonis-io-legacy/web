<?php
namespace Tonis\Tonis;

use Tonis\Di\Container;
use Tonis\Tonis\Factory\TonisFactory;
use Tonis\Tonis\TestAsset\TestSubscriber;
use Tonis\Package\PackageManager;
use Tonis\View\Strategy\PlatesStrategy;
use Tonis\View\Strategy\TwigStrategy;

/**
 * @coversDefaultClass \Tonis\Tonis\TonisPackage
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
            'tonis' => [
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
