<?php
namespace Tonis\Web\Package;

use Interop\Container\ContainerInterface;
use Tonis\Web;
use Tonis\Package\Feature;
use Tonis\Router\RouteCollection;
use Tonis\Web\Tonis;
use Tonis\Web\TonisConsole;

interface PackageInterface extends
    Feature\ConfigProviderInterface,
    Feature\NameProviderInterface,
    Feature\NamespaceProviderInterface,
    Feature\PathProviderInterface
{
    /**
     * @param Tonis $tonis
     */
    public function bootstrap(Tonis $tonis);

    /**
     * @param TonisConsole $console
     */
    public function bootstrapConsole(TonisConsole $console);

    /**
     * @param ContainerInterface $di
     */
    public function configureServices(ContainerInterface $di);

    /**
     * @param RouteCollection $routes
     */
    public function configureRoutes(RouteCollection $routes);
}
