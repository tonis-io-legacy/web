<?php
namespace Tonis\Web;

use Tonis\Di\Container;
use Tonis\Web\TestAsset\TestSubscriber;
use Tonis\Package\PackageManager;
use Tonis\View\Strategy\PlatesStrategy;
use Tonis\View\Strategy\TwigStrategy;

/**
 * @coversDefaultClass \Tonis\Web\AppPackage
 */
class AppPackageTest extends \PHPUnit_Framework_TestCase
{
    /** @var AppPackage */
    private $package;

    /**
     * @covers ::bootstrap
     */
    public function testBootstrap()
    {
        $app = (new AppFactory)->create();
        $di = $app->getServiceContainer();
        $di['config'] = [
            'tonis' => [
                'subscribers' => [
                    new TestSubscriber(),
                    TestSubscriber::class => TestSubscriber::class
                ]
            ]
        ];

        $this->package->bootstrap($app);
        $this->assertNotEmpty($app->getEventManager()->getListeners());
        $this->assertCount(2, $app->getEventManager()->getListeners('foo'));
    }

    /**
     * @covers ::configureServices
     */
    public function testConfigureServices()
    {
        $pm = new PackageManager;

        $services = new Container;
        $services->set(PackageManager::class, $pm, true);

        $this->package->configureServices($services);

        $this->assertTrue($services->has(PlatesStrategy::class));
        $this->assertTrue($services->has(TwigStrategy::class));
        $this->assertSame($pm->getMergedConfig(), $services['config']);
    }

    protected function setUp()
    {
        $this->package = new AppPackage();
    }
}
