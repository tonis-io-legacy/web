<?php
namespace Tonis\Tonis\Subscriber;

use Interop\Container\ContainerInterface;
use Tonis\Event\EventManager;
use Tonis\Event\SubscriberInterface;
use Tonis\Tonis\Package\PackageInterface;
use Tonis\Tonis\Tonis;
use Tonis\Tonis\TonisConsole;
use Tonis\Package\PackageManager;

final class ConsoleSubscriber implements SubscriberInterface
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
     * {@inheritDoc}
     */
    public function subscribe(EventManager $events)
    {
        $events->on(Tonis::EVENT_BOOTSTRAP, function () {
            $pm = $this->di->get(PackageManager::class);
            $console = $this->di->get(TonisConsole::class);

            foreach ($pm->getPackages() as $package) {
                if ($package instanceof PackageInterface) {
                    $package->bootstrapConsole($console);
                }
            }
        });
    }
}
