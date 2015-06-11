Dispatching is the process of taking information from a route and creating a result which can then be fed into the view. 
In most frameworks this is a controller or, in the case of micro-frameworks, a closure. Tonis doesn't make any assumptions 
about what your dispatchable is only that it can be called.

### Sample Dispatchables

 * A string (can be rendered by `Tonis\View\Strategy\StringStrategy`)
 * A callable: MyClass::method or [$myClass, 'method'] or ['MyClass', 'method']
 * Any object with the `__invoke()` magic method
 * Any object implementing `Tonis\Dispatcher\DispatchableInterface`
 * A closure `function() {}`
 
Return Results
--------------

Tonis expects all dispatchables to return a result it can work with. By default, this includes `array`, `string`, and 
any instance of `Tonis\View\ModelInterface`. If an invalid result is returned then Tonis will throw a 
`Tonis\Tonis\Exception\InvalidDispatchResultException`.

Valid return results and conversions are:

<dl>
    <dt>Return Result</dt>
    <dd>string</dd>
    <dt>API Result</dt>
    <dd><code>new StringModel(string)</code></dd>
    <dt>Web Result</dt>
    <dd><code>new StringModel(string)</code></dd>
</dl>

----

<dl>
    <dt>Return Result</dt>
    <dd>array</dd>
    <dt>API Result</dt>
    <dd><code>new JsonModel(array)</code></dd>
    <dt>Web Result</dt>
    <dd><code>new ViewModel(null, array)</code></dd>
    <dt>Extra Note</dt>
    <dd>
        If the <code>$$template</code> key is specified then the ViewModel will use it instead of <code>null</code> for 
        the template.
    </dd>
</dl>

----

<dl>
    <dt>Return Result</dt>
    <dd>Tonis\View\ModelInterface</dd>
    <dt>API Result</dt>
    <dd>No change</dd>
    <dt>Web Result</dt>
    <dd>No change</dd>
</dl>

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
