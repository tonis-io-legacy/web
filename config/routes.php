<?php
use Tonis\Router\RouteCollection;

return function(RouteCollection $routes) {
    $routes->get('home', '/', 'Tonis\Mvc\Action\HomeAction');
};
