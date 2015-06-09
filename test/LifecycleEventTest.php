<?php
namespace Tonis\Mvc;

use Tonis\Router\Route;
use Tonis\Router\RouteMatch;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequestFactory;

/**
 * @coversDefaultClass \Tonis\Mvc\LifecycleEvent
 */
class LifecycleEventTest extends \PHPUnit_Framework_TestCase
{
    /** @var LifecycleEvent */
    private $event;

    /**
     * @covers ::__construct
     * @covers ::getRequest
     */
    public function testGetRequest()
    {
        $request = ServerRequestFactory::fromGlobals();
        $event = new LifecycleEvent($request);
        $this->assertSame($request, $event->getRequest());
    }

    /**
     * @covers ::setResponse
     * @covers ::getResponse
     * @covers ::setRouteMatch
     * @covers ::getRouteMatch
     * @covers ::getDispatchResult
     * @covers ::setDispatchResult
     * @covers ::getRenderResult
     * @covers ::setRenderResult
     * @covers ::setException
     * @covers ::getException
     * @dataProvider getterSetterProvider
     */
    public function testGetterSetter($method, $value)
    {
        $setter = 'set' . $method;
        $getter = 'get' . $method;

        $this->event->$setter($value);
        $this->assertSame($value, $this->event->$getter());
    }

    /**
     * @covers ::getResponse
     */
    public function testGetResponseLazyLoads()
    {
        $this->assertInstanceOf(Response::class, $this->event->getResponse());
    }

    public function getterSetterProvider()
    {
        return [
            ['Exception', new \RuntimeException],
            ['Response', new Response],
            ['RouteMatch', new RouteMatch(new Route('/foo', 'handler'))],
            ['DispatchResult', new \StdClass],
            ['RenderResult', new \StdClass]
        ];
    }

    protected function setUp()
    {
        $this->event = new LifecycleEvent(ServerRequestFactory::fromGlobals());
    }
}
