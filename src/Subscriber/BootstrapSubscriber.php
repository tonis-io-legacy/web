<?php
namespace Tonis\Mvc\Subscriber;

use Tonis\Event\EventManager;
use Tonis\Event\SubscriberInterface;
use Tonis\Mvc\LifecycleEvent;
use Tonis\Mvc\Package;
use Tonis\Mvc\Package\PackageInterface;
use Tonis\Mvc\Tonis;

final class BootstrapSubscriber implements SubscriberInterface
{
    /**
     * {@inheritDoc}
     */
    public function subscribe(EventManager $events)
    {
        $events->on(Tonis::EVENT_BOOTSTRAP, [$this, 'bootstrapPackageManager']);
        $events->on(Tonis::EVENT_BOOTSTRAP, [$this, 'bootstrapPackages']);
    }

    /**
     * @param LifecycleEvent $event
     */
    public function bootstrapPackageManager(LifecycleEvent $event)
    {
        $tonis = $event->getTonis();
        $config = $tonis->getConfig();
        $pm = $tonis->getPackageManager();
        $pm->add(Package::class);

        foreach ($config->getPackages() as $package) {
            if ($package[0] == '?') {
                if (!$tonis->isDebugEnabled()) {
                    continue;
                }
                $package = substr($package, 1);
            }
            $pm->add($package);
        }

        $pm->load();
    }

    /**
     * @param LifecycleEvent $event
     */
    public function bootstrapPackages(LifecycleEvent $event)
    {
        $tonis = $event->getTonis();
        $di = $tonis->di();

        foreach ($tonis->getPackageManager()->getPackages() as $package) {
            if ($package instanceof PackageInterface) {
                $package->configureServices($tonis->di());
                $package->bootstrap($tonis);
                $package->configureRoutes($tonis->routes());

                $di[$package->getName()] = $package->getConfig();
            }
        }
    }
}
