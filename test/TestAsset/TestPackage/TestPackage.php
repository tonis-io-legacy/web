<?php
namespace Tonis\Web\TestAsset\TestPackage;

use Interop\Container\ContainerInterface;
use Tonis\Router\Router;
use Tonis\Web\Package\AbstractPackage;

class TestPackage extends AbstractPackage
{
    public function getPath()
    {
        return __DIR__;
    }

    public function configureServices(ContainerInterface $services)
    {
        $services->set('foo', function() {
            return 'bar';
        });
    }

    public function configureRoutes(Router $router)
    {
        $router->get('/foo', 'handler');
    }
}
