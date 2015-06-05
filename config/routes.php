<?php

use Tonis\Router\RouteCollection;

return function(RouteCollection $routes) {
    $routes->get('/', function() {
        return 'Tonis Landing Page';
    });
};
