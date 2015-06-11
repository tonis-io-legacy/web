<?php
namespace Tonis\Web\TestAsset\TestPackage;

use Interop\Container\ContainerInterface;
use Tonis\Web\Package\AbstractPackage;
use Tonis\Router\RouteCollection;

class TestPackage extends AbstractPackage
{
    public function getPath()
    {
        return __DIR__;
    }

    public function configureServices(ContainerInterface $di)
    {
        $di->set('foo', function() {
            return 'bar';
        });
    }

    public function configureRoutes(RouteCollection $routes)
    {
        $routes->get('/foo', 'handler');
    }
}
