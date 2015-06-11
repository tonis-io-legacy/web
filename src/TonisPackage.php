<?php
namespace Tonis\Web;

use Interop\Container\ContainerInterface;
use Tonis\Di\ContainerUtil;
use Tonis\Web\Factory\PlatesStrategyFactory;
use Tonis\Web\Factory\TwigStrategyFactory;
use Tonis\Web\Package\AbstractPackage;
use Tonis\Package\PackageManager;
use Tonis\View\Strategy\PlatesStrategy;
use Tonis\View\Strategy\TwigStrategy;

class TonisPackage extends AbstractPackage
{
    /**
     * @param Tonis $tonis
     */
    public function bootstrap(Tonis $tonis)
    {
        $di = $tonis->di();
        $config = $tonis->getConfig();
        $packageConfig = $di['config']['tonis'];

        $subscribers = array_merge($config->getSubscribers(), $packageConfig['subscribers']);
        foreach ($subscribers as $subscriber => $factory) {
            if (is_int($subscriber)) {
                $subscriber = $factory;
            } else {
                $di->set($subscriber, $factory);
            }

            $tonis->events()->subscribe(ContainerUtil::get($di, $subscriber));
        }
    }

    /**
     * {@inheritDoc}
     */
    public function configureServices(ContainerInterface $di)
    {
        $di['config'] = $di->get(PackageManager::class)->getMergedConfig();

        $di->set(PlatesStrategy::class, PlatesStrategyFactory::class);
        $di->set(TwigStrategy::class, TwigStrategyFactory::class);
    }
}
