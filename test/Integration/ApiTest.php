<?php
namespace Tonis\Web\Integration;

use Tonis\Web\Factory\TonisFactory;
use Tonis\Web\Tonis;

class ApiTest extends \PHPUnit_Framework_TestCase
{
    /** @var Tonis */
    private $tonis;

    public function testApiLoads()
    {
        $this->tonis->routes()->get('/', function () {
            return ['foo' => 'bar'];
        });

        $response = $this->tonis->__invoke();
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(['application/json'], $response->getHeader('Content-Type'));
        $this->assertSame('{"foo":"bar"}', (string) $response->getBody());
    }

    public function testApiHandlesInvalidRoutes()
    {
        $response = $this->tonis->__invoke();

        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame(['application/json'], $response->getHeader('Content-Type'));
        $this->assertSame('{"error":"Route could not be matched","path":"\/"}', (string) $response->getBody());
    }

    public function testApiHandlesDispatchExceptions()
    {
        $this->tonis->routes()->get('/', function () {
            throw new \RuntimeException('foobar');
        });

        $response = $this->tonis->__invoke();
        $json = json_decode((string) $response->getBody(), true);

        $this->assertSame(500, $response->getStatusCode());
        $this->assertSame(['application/json'], $response->getHeader('Content-Type'));
        $this->assertSame('An error has occurred', $json['error']);
        $this->assertSame('foobar', $json['message']);
        $this->assertSame('RuntimeException', $json['exception']);
    }

    public function testApiHandlesInvalidDispatchResults()
    {
        $this->tonis->routes()->get('/', function () {
            return null;
        });

        $response = $this->tonis->__invoke();
        $json = json_decode((string) $response->getBody(), true);

        $this->assertSame(500, $response->getStatusCode());
        $this->assertSame(['application/json'], $response->getHeader('Content-Type'));
        $this->assertSame('An error has occurred', $json['error']);
        $this->assertSame('Tonis\Web\Exception\InvalidDispatchResultException', $json['exception']);
    }

    protected function setUp()
    {
        $this->tonis = (new TonisFactory)->createApi();
    }
}
