Tonis is PSR-7 compatible and takes `Psr\Http\Message\RequestInterface` as input and outputs a 
`Psr\Http\Message\ResponseInterface`. You can plug Tonis into a middleware framework like 
[Stratigility](https://github.com/zendframework/zend-stratigility) to have separate applications running based on the path.
Furthemore, with Tonis' event architecture you can streamline each application to get the most performance based on what
the application context is.

Example
-------

The following `index.php` will serve the same `ExamplePackage` through Tonis. The `ExamplePackage`
defines a single route to a single dispatchable which returns an array. `createWeb()` and `createApi()` are factory
methods that create tailored Tonis subscribers based on the application context.

```php
// route
$routes->get('/{name}', [IndexController::class, 'testAction']);
```

```php
// controller
namespace ExamplePackage\Home;

class IndexController
{
    public function testAction()
    {
        return ['foo' => 'bar'];
    }
}

```

```php
// index.php
require __DIR__ . '/../vendor/autoload.php';

$config = ['packages' => [ExamplePackage\ExamplePackage::class]];
$tonisFactory = new Tonis\Web\Factory\TonisFactory;

$app = new Zend\Stratigility\MiddlewarePipe();

// createApi() uses subscribers optimized for JSON APIs
$app->pipe('/api', $tonisFactory->createApi($config));

// createWeb() uses subscribers optimized for your typical web application
$app->pipe($tonisFactory->createWeb($config));

$server = Zend\Diactoros\Server::createServer($app, $_SERVER, $_GET, $_POST, $_COOKIE, $_FILES);
$server->listen();
```

Now that the middleware pipe is established you can visit `/{name}` or `/api/{name}` and the dispatchable will
return the same result. However, during rendering the subscribers registered to each instance changes the response body.

When visiting `/SpiffyJr` the response body is `Hello SpiffyJr, welcome to Tonis.`.

When visiting `/api/SpiffyJr` the response body is `{"name": "SpiffyJr"}`.
