You define routes by using the `Tonis\Router\RouteCollection` available to Tonis. Additionally, packages may
use the `configureRoutes()` method to define their routes.

Basic Routes
------------

You can add routes to the route collection using the `add()` method. 

```php
$routes->add('/', function() {
    return 'Hey Tonis';
});
```

HTTP Verbs
----------

The route collection provides methods mapped to HTTP verbs as a convenience.

```php
$routes->get('/', function () { ... });
$routes->post('/', function () { ... });
$routes->patch('/', function () { ... });
$routes->put('/', function () { ... });
$routes->delete('/', function () { ... });
```

Route Handler
--------------

The second parameter of any method above is the handler. The handler is what `Tonis\Dispatcher` uses to create the 
dispatchable resource and generate a dispatch result. View the [Dispatching documentation](/basics/dispatching) for 
more information.

Route Parameters
----------------

```php
// required name parameter - matches /hello/tonis
$routes->get('/hello/{name}', ...);

// optional name parameter - matches /hello or /hello/tonis
$routes->get('/hello{/name?}', ...);
```

**It's important to note the "/" inside the "{". If you leave this outside the curl brace it is not optional and will
be required for the route to match!**

```php
// parameter constraints - matches /hello/tonis but not /hello/1234
$routes->get('/hello/{name:\w+}', ...);

// the kitchen sink (id is required as a digit, name is optional and matches a-z and A-Z
// matches /hello/1 or /hello/1-tonis
$routes->get('/hello/{id:\d+}{-name?:[a-zA-Z]+}');
```

Assembling URLs
---------------

In order to assemble a route you must have given it a name.

```php
$routes->get('/', function () {}, 'name');
```

To generate a url from a route you may use the `assemble()` method on the `RouteCollection`.
 
```php
$routes->assemble('name')
```

Methods
-------

You can opt out of using the HTTP verbs and setting methods manually on a route by using the `methods()` method.
This allows you to tell a route that it can match on multiple HTTP verbs.

Default: `[]`

```php
$routes
    ->add('/', function() {})
    ->methods(['GET', 'POST']);
```

Defaults
--------

You can set parameter defaults for parameters that are not specified by using the `defaults()` method.

Default: `[]`

```php
$routes
    ->get('/{name}', function() {})
    ->defaults(['name' => 'Anonymous']);
```

Accepts
-------

You can set what Accept header the match will route by using the `accepts()` method.

Default: `['*']`

```php
$routes
    ->get('/api/people.json', function() {})
    ->accepts(['application/json']);
```

Secure
------

You can tell the route to only allow secure requests by using the `secure()` method. Passing
`true` will match secure connections, `false` will only match insecure connections, and `null` will
allow any connection type.

Default: `null`

```php
$routes
    ->get('/user/login', function() {})
    ->secure(true);
    
$routes
    ->get('/insecure', function() {})
    ->secure(false);
    
$routes
    ->get('/', function() {})
    ->secure(null);
```
