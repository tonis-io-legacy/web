<?php
namespace Tonis\Mvc\Package;

use Tonis\Di\Container;
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
     * @param Container $di
     */
    public function configureServices(Container $di);

    /**
     * @param RouteCollection $routes
     */
    public function configureRoutes(RouteCollection $routes);
}
