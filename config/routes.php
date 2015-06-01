<?php
use Tonis\Router\Collection;

return function(Collection $routes) {
    $routes->get('/', function() {
        return 'Tonis Landing Page';
    });
};
