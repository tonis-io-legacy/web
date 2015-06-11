Dispatching is the process of taking information from a route and creating a result which can then be fed into the view. 
In most frameworks this is a controller or, in the case of micro-frameworks, a closure. Tonis doesn't make any assumptions 
about what your dispatchable is only that it can be called.

### Sample Dispatchables

 * A string (can be rendered by `Tonis\View\Strategy\StringStrategy`)
 * A callable: MyClass::method or [$myClass, 'method'] or ['MyClass', 'method']
 * Any object with the `__invoke()` magic method
 * Any object implementing `Tonis\Dispatcher\DispatchableInterface`
 * A closure `function() {}`

Examples
--------

Dispatchables are up to the user to implement but a few examples are listed below to get you started:

### Traditional Controller

Your standard controller that most major frameworks use.

```php
class MyController
{
    public function indexAction() { ... }
    public function viewAction($id) { ... }
}

$routes->get('/', [MyController::class, 'indexAction']);
$routes->get('/{id}', [MyController::class, 'viewAction']);
```

### Micro-Framework Closure

Your standard micro-framework approach with a single closure.

```php
$routes->get('/', function() { ... });
$routes->get('/{id}', function($id) { ... });
```

### Single Purpose Action

Actions extract the individual methods from Controllers and make them their own classes. This has the benefit of keeping
your dependencies separate from actions that may or may not use them. The downside, however, is more classes to create.

```php
class MyAction
{
    public function __invoke() { ... }
}

class MyViewAction
{
    public function __invoke($id) { ... }
}

$routes->get('/', MyAction::class);
$routes->get('/{id}', MyViewAction::class]);
```

Using the Service Container
---------------------------

If your dispatchable is [registered with the service container](/basics/services) you can pass the service name to the
route and it will be pulled from the service container.
 
```php
class MyController
{
    public function __construct(Foo $foo) { ... }
    public function indexAction() { ... }
    public function viewAction($id) { ... }
}

$di->set('my.controller', function (\Tonis\Di\Container $di) {
    return new MyController($di->get('foo'));
});
```
