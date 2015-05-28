<?php
namespace Tonis\Mvc\Package;

use Interop\Container\ContainerInterface;
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
     * @param ContainerInterface $di
     */
    public function configureDi(ContainerInterface $di);

    /**
     * @param RouteCollection $routes
     */
    public function configureRoutes(RouteCollection $routes);
}
