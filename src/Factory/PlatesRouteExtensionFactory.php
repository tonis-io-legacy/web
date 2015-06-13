<?php
namespace Tonis\Web\Factory;

use Interop\Container\ContainerInterface;
use Tonis\Router\Plates\RouteExtension;
use Tonis\Router\Router;

class PlatesRouteExtensionFactory
{
    /**
     * @param ContainerInterface $services
     * @return RouteExtension
     */
    public function __invoke(ContainerInterface $services)
    {
        return new RouteExtension($services->get(Router::class));
    }
}
