<?php
namespace Tonis\Web\Package;

use Interop\Container\ContainerInterface;
use Tonis\Router\Router;
use Tonis\Web;
use Tonis\Package\Feature;
use Tonis\Web\App;
use Tonis\Web\Console;

interface PackageInterface extends
    Feature\ConfigProviderInterface,
    Feature\NameProviderInterface,
    Feature\NamespaceProviderInterface,
    Feature\PathProviderInterface
{
    /**
     * @param App $app
     */
    public function bootstrap(App $app);

    /**
     * @param Console $console
     */
    public function bootstrapConsole(Console $console);

    /**
     * @param ContainerInterface $di
     */
    public function configureServices(ContainerInterface $di);

    /**
     * @param Router $router
     */
    public function configureRoutes(Router $router);
}
