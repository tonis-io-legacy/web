<?php
namespace Tonis\Web\Subscriber;

use Interop\Container\ContainerInterface;
use Tonis\Event\EventManager;
use Tonis\Event\SubscriberInterface;
use Tonis\Web\Package\PackageInterface;
use Tonis\Web\App;
use Tonis\Web\Console;
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
        $events->on(App::EVENT_BOOTSTRAP, function () {
            $pm = $this->di->get(PackageManager::class);
            $console = $this->di->get(Console::class);

            foreach ($pm->getPackages() as $package) {
                if ($package instanceof PackageInterface) {
                    $package->bootstrapConsole($console);
                }
            }
        });
    }
}
