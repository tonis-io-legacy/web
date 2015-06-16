<?php
namespace Tonis\Web\Integration;

use Tonis\Web\App;
use Tonis\Web\AppFactory;

class ApiTest extends \PHPUnit_Framework_TestCase
{
    /** @var App */
    private $app;

    public function testApiLoads()
    {
        $this->app->getRouter()->get('/', function () {
            return ['foo' => 'bar'];
        });

        $response = $this->app->__invoke();
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(['application/json'], $response->getHeader('Content-Type'));
        $this->assertSame('{"foo":"bar"}', (string) $response->getBody());
    }

    public function testApiHandlesInvalidRoutes()
    {
        $response = $this->app->__invoke();

        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame(['application/json'], $response->getHeader('Content-Type'));
        $this->assertSame('{"error":"Route could not be matched","path":"\/"}', (string) $response->getBody());
    }

    public function testApiHandlesDispatchExceptions()
    {
        $this->app->getRouter()->get('/', function () {
            throw new \RuntimeException('foobar');
        });

        $response = $this->app->__invoke();
        $json = json_decode((string) $response->getBody(), true);

        $this->assertSame(500, $response->getStatusCode());
        $this->assertSame(['application/json'], $response->getHeader('Content-Type'));
        $this->assertSame('An error has occurred', $json['error']);
        $this->assertSame('foobar', $json['message']);
        $this->assertSame('RuntimeException', $json['exception']);
    }

    public function testApiHandlesInvalidDispatchResults()
    {
        $this->app->getRouter()->get('/', function () {
            return null;
        });

        $response = $this->app->__invoke();
        $json = json_decode((string) $response->getBody(), true);

        $this->assertSame(500, $response->getStatusCode());
        $this->assertSame(['application/json'], $response->getHeader('Content-Type'));
        $this->assertSame('An error has occurred', $json['error']);
        $this->assertSame('Tonis\Web\Exception\InvalidDispatchResultException', $json['exception']);
    }

    protected function setUp()
    {
        $this->app = (new AppFactory)->createApi(['debug' => true]);
    }
}
