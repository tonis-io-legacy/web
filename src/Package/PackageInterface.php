<?php
namespace Tonis\Mvc\Package;

use Interop\Container\ContainerInterface;
use Tonis\Mvc;
use Tonis\Package\Feature;
use Tonis\Router\RouteCollection;

interface PackageInterface extends
    Feature\ConfigProviderInterface,
    Feature\NameProviderInterface,
    Feature\NamespaceProviderInterface,
    Feature\PathProviderInterface
{
    /**
     * @param Mvc\Tonis $tonis
     */
    public function bootstrap(Mvc\Tonis $tonis);

    /**
     * @param Mvc\TonisConsole $console
     */
    public function bootstrapConsole(Mvc\TonisConsole $console);

    /**
     * @param ContainerInterface $di
     */
    public function configureServices(ContainerInterface $di);

    /**
     * @param RouteCollection $routes
     */
    public function configureRoutes(RouteCollection $routes);
}
