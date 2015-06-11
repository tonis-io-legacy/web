<?php
namespace Tonis\Tonis\Package;

use Interop\Container\ContainerInterface;
use Tonis\Tonis;
use Tonis\Package\Feature;
use Tonis\Router\RouteCollection;

interface PackageInterface extends
    Feature\ConfigProviderInterface,
    Feature\NameProviderInterface,
    Feature\NamespaceProviderInterface,
    Feature\PathProviderInterface
{
    /**
     * @param Tonis\Tonis $tonis
     */
    public function bootstrap(Tonis\Tonis $tonis);

    /**
     * @param Tonis\TonisConsole $console
     */
    public function bootstrapConsole(Tonis\TonisConsole $console);

    /**
     * @param ContainerInterface $di
     */
    public function configureServices(ContainerInterface $di);

    /**
     * @param RouteCollection $routes
     */
    public function configureRoutes(RouteCollection $routes);
}
