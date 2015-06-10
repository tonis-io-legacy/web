<?php
namespace Tonis\Mvc\Integration;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Tonis\Mvc\Factory\TonisFactory;
use Tonis\Mvc\Tonis;

class ApiTest extends \PHPUnit_Framework_TestCase
{
    /** @var Tonis */
    private $tonis;

    public function testApiLoads()
    {
        $this->tonis->routes()->get('/', function () {
            return ['foo' => 'bar'];
        });

        $response = $this->tonis->run();
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(['application/json'], $response->getHeader('Content-Type'));
        $this->assertSame('{"foo":"bar"}', (string) $response->getBody());
    }

    public function testApiHandlesInvalidRoutes()
    {
        $response = $this->tonis->run();

        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame(['application/json'], $response->getHeader('Content-Type'));
        $this->assertSame('{"error":"Route could not be matched","path":"\/"}', (string) $response->getBody());
    }

    public function testApiHandlesDispatchExceptions()
    {
        $this->tonis->routes()->get('/', function () {
            throw new \RuntimeException('foobar');
        });

        $response = $this->tonis->run();
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

        $response = $this->tonis->run();
        $json = json_decode((string) $response->getBody(), true);

        $this->assertSame(500, $response->getStatusCode());
        $this->assertSame(['application/json'], $response->getHeader('Content-Type'));
        $this->assertSame('An error has occurred', $json['error']);
        $this->assertSame('Tonis\Mvc\Exception\InvalidDispatchResultException', $json['exception']);
    }

    protected function setUp()
    {
        $this->tonis = (new TonisFactory)->createApi();
    }
}
