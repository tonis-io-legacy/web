<?php
namespace Tonis\Mvc\Package;

use Tonis\Di\Container;
use Tonis\PackageManager\Feature\ConfigProviderInterface;
use Tonis\PackageManager\Feature\NameProviderInterface;
use Tonis\PackageManager\Feature\NamespaceProviderInterface;
use Tonis\PackageManager\Feature\PathProviderInterface;
use Tonis\Router\RouteCollection;

interface PackageInterface extends
    ConfigProviderInterface,
    NameProviderInterface,
    NamespaceProviderInterface,
    PathProviderInterface
{
    /**
     * @param Container $di
     */
    public function configureDi(Container $di);

    /**
     * @param RouteCollection $routes
     */
    public function configureRoutes(RouteCollection $routes);
}
