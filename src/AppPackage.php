<?php
namespace Tonis\Web;

use Interop\Container\ContainerInterface;
use Tonis\Di\ContainerUtil;
use Tonis\Router\Plates\RouteExtension;
use Tonis\Web\Factory\PlatesRouteExtensionFactory;
use Tonis\Web\Factory\PlatesStrategyFactory;
use Tonis\Web\Factory\TwigStrategyFactory;
use Tonis\Web\Package\AbstractPackage;
use Tonis\Package\PackageManager;
use Tonis\View\Strategy\PlatesStrategy;
use Tonis\View\Strategy\TwigStrategy;

class AppPackage extends AbstractPackage
{
    /**
     * @param App $app
     */
    public function bootstrap(App $app)
    {
        $services = $app->getServiceContainer();
        $config = $app->getConfig();
        $packageConfig = $services['config']['tonis'];

        $subscribers = array_merge($config->getSubscribers(), $packageConfig['subscribers']);
        foreach ($subscribers as $subscriber => $factory) {
            if (is_int($subscriber)) {
                $subscriber = $factory;
            } else {
                $services->set($subscriber, $factory);
            }

            $app->getEventManager()->subscribe(ContainerUtil::get($services, $subscriber));
        }
    }

    /**
     * {@inheritDoc}
     */
    public function configureServices(ContainerInterface $services)
    {
        $services['config'] = $services->get(PackageManager::class)->getMergedConfig();

        $services->set(RouteExtension::class, PlatesRouteExtensionFactory::class);
        $services->set(PlatesStrategy::class, PlatesStrategyFactory::class);
        $services->set(TwigStrategy::class, TwigStrategyFactory::class);
    }
}
