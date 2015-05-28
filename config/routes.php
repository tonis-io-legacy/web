<?php
use Tonis\Router\RouteCollection;

return function(RouteCollection $routes) {
    $routes->get('home', '/', function() {
        return 'Tonis MVC Landing Page';
    });
};
