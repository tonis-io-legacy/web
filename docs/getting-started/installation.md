Tonis uses [Composer](https://getcomposer.org/download/) to manage dependencies and is required. Once composer is 
installed you can install Tonis using the following command:

As a Micro-Framework
--------------------

To use Tonis as micro-framework you need to add `tonis/tonis` to `composer.json`.

```sh
composer init
composer require tonis/tonis
```

Once complete you use the following `index.php` to get started.

```php
<?php
require __DIR__ . '/vendor/autoload.php';

$tonis = (new \Tonis\Tonis\Factory\TonisFactory)->createWeb();
$routes = $tonis->routes();

$routes->get('/hello/{name}', function ($name) {
    return 'Hello ' . $name;
});

echo $tonis->run()->getBody();
```

As Middleware
-------------

See the [Middleware documentation](/advanced/middleware) for more information.

Server Requirements
-------------------

Tonis has the following requirement:

  * PHP 5.5 or greater
