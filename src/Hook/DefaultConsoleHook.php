<?php
namespace Tonis\Mvc\Hook;

use Tonis\Mvc\Package\PackageInterface;
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
    public function onBootstrap()
    {
        foreach ($this->console->getTonis()->getPackageManager()->getPackages() as $package) {
            if ($package instanceof PackageInterface) {
                $package->bootstrapConsole($this->console);
            }
        }
    }
}
