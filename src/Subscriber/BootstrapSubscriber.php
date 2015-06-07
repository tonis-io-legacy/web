<?php
namespace Tonis\Mvc\Subscriber;

use Interop\Container\ContainerInterface;
use Tonis\Event\EventManager;
use Tonis\Event\SubscriberInterface;
use Tonis\Mvc\Package\PackageInterface;
use Tonis\Mvc\Tonis;

final class BootstrapSubscriber implements SubscriberInterface
{
    /** @var ContainerInterface */
    private $di;

    /**
     * @param ContainerInterface $di
     */
    public function __construct(ContainerInterface $di)
    {
        $this->di = $di;
    }

    /**
     * @param EventManager $events
     * @return void
     */
    public function subscribe(EventManager $events)
    {
        $events->on(Tonis::EVENT_BOOTSTRAP, [$this, 'bootstrapPackageManager']);
    }

    public function bootstrapPackageManager()
    {
        /** @var Tonis $tonis */
        $tonis = $this->di->get(Tonis::class);

        $pm = $tonis->getPackageManager();
        $pm->load();

        foreach ($pm->getPackages() as $package) {
            if ($package instanceof PackageInterface) {
                $package->configureServices($tonis->di());
                $package->bootstrap($tonis);
                $package->configureRoutes($tonis->routes());
            }
        }
    }
}
