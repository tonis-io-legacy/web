<?php
namespace Tonis\Mvc\Hook;

use Tonis\Mvc\Package\PackageInterface;
use Tonis\Mvc\Tonis;
use Tonis\Mvc\TonisConsole;

final class DefaultConsoleHook extends AbstractTonisHook
{
    /** @var TonisConsole */
    private $console;

    /**
     * @param TonisConsole $console
     */
    public function __construct(TonisConsole $console)
    {
        $this->console = $console;
    }

    /**
     * {@inheritDoc}
     */
    public function onBootstrap(Tonis $app, array $config)
    {
        $pm = $app->getPackageManager();
        foreach ($pm->getPackages() as $package) {
            if ($package instanceof PackageInterface) {
                $package->bootstrapConsole($this->console);
            }
        }
    }
}
