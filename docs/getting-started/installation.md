Tonis uses [Composer](https://getcomposer.org/download/) to manage dependencies and is required. Once composer is 
installed you can install Tonis using the following command:

As a Micro-Framework
--------------------

To use Tonis as micro-framework you need to add `tonis/web` to `composer.json`. You will also need a PSR-7
implementation like `zendframework/zend-diactoros`.

```sh
composer require tonis/web zendframework/zend-diactoros
```

Once complete you use one of the following `index.php` to get started.

```php
<?php
require __DIR__ . '/vendor/autoload.php';

$tonis = (new \Tonis\Web\Factory\TonisFactory)->createWeb();
$routes = $tonis->routes();

$routes->get('/hello/{name}', function ($name) {
    return sprintf('Hello %s, welcome to Tonis', $name);
});

$tonis->run();
```

As Middleware
-------------

See the [Middleware documentation](/advanced/middleware) for more information.

Server Requirements
-------------------

Tonis has the following requirement:

  * PHP 5.5 or greater
