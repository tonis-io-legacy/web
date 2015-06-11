Tonis features a light-weight and simple dependency injection container to help with inversion of control. You can 
read more about inversion of control on [Martin Fowler's site](http://martinfowler.com/articles/injection.html).

Features
--------

 * Parameter management
 * Simple API
 * Service decoration.
 * Recursion detection.
  
Parameters
----------

You can assign parameters to the container using array notation. All parameters are available later to setup your 
other services.

```php
$di['foo'] = 'bar';
$di['debug'] = false;
```

Services
--------

You can define your services using the following:

  * Passing a string of the fully qualified class name
  * Passing the created object
  * Passing an instance of `Tonis\Di\ServiceFactoryInterface`
  * Using any callable
  * Any combination of the above
  
### Setting Services

```php
// using the fqcn
$di->set('stdclass', 'StdClass');

// setting the object directly
$di->set('stdclass', new StdClass);

// using a callable
$di->set('stdclass', function (\Tonis\Di\Container $di) {
    return new StdClass;
});

// using an instance of ServiceFactoryInterface
class StdClassFactory implements \Tonis\Di\ServiceFactoryInterface
{
    public function createService(\Tonis\Di\Container $di) {
        return new StdClass;
    }
}
```

### Check if a service exists

```php
$di->has('StdClass'); // true/false
```

### Retrieving services

```php
$di->get('StdClass');
```

Setting a raw service
---------------------

Sometimes you have a callable that you want to set as a service without the container trying to resolve the callable. 
To do this pass true as third argument to the `set` method.
 
```php
class MyClass
{
    public function __invoke()
    {
        return new StdClass;
    }
}

$di = $tonis->di();
// normally get(MyClass::class) will return StdClass
$di->set(MyClass::class, MyClass::class);

// now get(MyClass::class) will return an instance of MyClass instead  
$di->set(MyClass::class, MyClass::class, true);
```

Decorating Services
-------------------

Tonis features powerful service decoration which let's you modify your service configuration at run-time. This is
very powerful when coupled with package management to allow customization of services.

### Using decorate

The `decorate` method allows you to modify the service after it has been created by the container. The `decorate` method
accepts a closure or an instance of `Tonis\Di\ServiceDecoratorInterface`.

```php
$di->set(StdClass::class, new StdClass);
$di->decorate(StdClass::class, function (\Tonis\Di\Container $di, StdClass $stdclass) {
    $stdclass->foo = 'bar';
});
```

You could use decorators to conditionally modify debug settings at run-time or setup logging, caching, etc. based on 
the environment. This is especially useful when third-party packages don't provide a way to configure their services
through configuration.

### Using wrap

`wrap` is similar to `decorate` but more powerful and allows you to completely replace the service if you wish. 

```php
class Dog
{
    private $sleep = false;
    public function speak() { echo $this->sleep ? '' : 'Woof!'; }
    public function sleep() { $this->sleep = true; }
}

class MyDog
{
    public function __construct(Dog $dog) { $this->dog = $dog; }
    public function speak() { echo 'My: ' . $this->dog->speak(); }
}

$di->set(Dog::class, Dog::class);

// tell the dog to sleep so it doesn't speak
$di->wrap(Dog::class, function (\Tonis\Di\Container $di, $serviceName, $callable) {
    $dog = $callable();
    $dog->sleep();
    
    return $dog;
});

// I want to use my own dog not the dog the neighbor (package) has
$di->wrap(Dog::class, function (\Tonis\Di\Container $di, $serviceName, $callable) {
    return new MyDog($callable());
});
```

A good use-case for wrap is modifying the service definitions provided by third-party packages. For example, if there
is a particularly heavy service you could wrap it in a Lazy Proxy to delay the execution until required.

Best Practices
--------------

When creating your services you generally want to avoid injecting the container directly into your services. There are 
situations where this can be useful but doing it too much can make maintenance and testing a nightmare. Instead,
you should use factories to create your services with the dependencies your service requires.

```php
use Tonis\Di\Container;

// do not do this
$di->set('MyClass', function (Container $di) {
    return new MyClass($di);
});

// do this instead
$di->set('MyClass', function (Container $di) {
    return new MyClass($di->get('dep1'), $di->get('dep2'));
});
```
