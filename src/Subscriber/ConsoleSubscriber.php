<?php
namespace Tonis\Mvc\Subscriber;

use Tonis\Event\EventManager;
use Tonis\Event\SubscriberInterface;
use Tonis\Mvc\Package\PackageInterface;
use Tonis\Mvc\Tonis;
use Tonis\Mvc\TonisConsole;
use Tonis\Package\PackageManager;

final class ConsoleSubscriber implements SubscriberInterface
{
    /** @var TonisConsole */
    private $console;
    /** @var PackageManager */
    private $packageManager;

    /**
     * @param TonisConsole $console
     */
    public function __construct(TonisConsole $console, PackageManager $packageManager)
    {
        $this->console = $console;
        $this->packageManager = $packageManager;
    }

    /**
     * {@inheritDoc}
     */
    public function subscribe(EventManager $events)
    {
        $events->on(Tonis::EVENT_BOOTSTRAP, function () {
            foreach ($this->packageManager->getPackages() as $package) {
                if ($package instanceof PackageInterface) {
                    $package->bootstrapConsole($this->console);
                }
            }
        });
    }
}
