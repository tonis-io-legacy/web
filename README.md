# Tonis

[![Build Status](https://scrutinizer-ci.com/g/tonis-io/tonis/badges/build.png?b=master)](https://scrutinizer-ci.com/g/tonis-io/tonis/build-status/master)
[![Code Coverage](https://scrutinizer-ci.com/g/tonis-io/tonis/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/tonis-io/tonis/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/tonis-io/tonis/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/tonis-io/tonis/?branch=master)

Tonis is a PSR-7 micro-framework that can also be used as MVC middleware. Tonis was built for flexibility and performance
while still having the modularity of larger frameworks. Tonis features an event-driven architecture that allows you to 
easily customize the entire life-cycle for micro-services, apis, or even full on sites.

Tonis is built with best practices in mind and features 100% unit test coverage, integration tests, and high quality code.
No pull request will ever be merged that violates any of these standards.

Quick Start
-----------

```sh
composer require tonis/tonis
```

```php
<?php
require __DIR__ . '/vendor/autoload.php';

$tonis = (new \Tonis\Tonis\Factory\TonisFactory)->createWeb();
$routes = $tonis->routes();

$routes->get('/hello/{name}', function ($name) {
    return sprintf('Hello %s, welcome to Tonis', $name);
});

$tonis->run();
```

Documentation
-------------

Full documentation can be found on the [Tonis GitHub page](https://docs.tonis.io) or in the `docs/` 
subdirectory.
