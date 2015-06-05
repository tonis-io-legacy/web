<?php

use Tonis\Router\RouteCollection;

return function (RouteCollection $routes) {
    $routes->get('/foo', 'handler');
};
