<?php
namespace Tonis\Web;

use Psr\Http\Message\ResponseInterface;
use Tonis\Di\Container;
use Tonis\Event\EventManager;
use Tonis\Router\Router;
use Tonis\Web\TestAsset\NewRequestTrait;
use Tonis\Web\TestAsset\TestEmitter;
use Tonis\Web\TestAsset\TestPackage\TestPackage;
use Tonis\Package\PackageManager;
use Tonis\Router\Route;
use Tonis\Router\RouteMatch;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequestFactory;

/**
 * @coversDefaultClass \Tonis\Web\App
 */
class TonisTest extends \PHPUnit_Framework_TestCase
{
    use NewRequestTrait;

    /** @var App */
    private $app;

    /**
     * @covers ::run
     */
    public function testRun()
    {
        $emitter = new TestEmitter;
        $app = (new AppFactory)->create();
        $app->run($emitter);

        $this->assertInstanceOf(ResponseInterface::class, $emitter->getResponse());
    }

    /**
     * @covers ::__invoke
     */
    public function testInvoke()
    {
        $request = ServerRequestFactory::fromGlobals();

        $app = (new AppFactory)->create();
        $app->__invoke($request);

        $event = $app->getLifecycleEvent();
        $this->assertSame($request, $event->getRequest());

        $response = new Response;
        $app->__invoke($request, $response);

        $event = $app->getLifecycleEvent();
        $this->assertSame($request, $event->getRequest());
        $this->assertSame($response, $event->getResponse());
    }

    /**
     * @covers ::respond
     */
    public function testRespond()
    {
        $response = new Response;

        $this->app->getEventManager()->on(App::EVENT_RESPOND, function (LifecycleEvent $event) use ($response) {
            $response->getBody()->write('foobar');
            $event->setResponse($response);
        });

        $this->app->route();
        $this->app->respond();
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame('foobar', (string) $response->getBody());
    }

    /**
     * @covers ::getEventManager
     */
    public function testGetEventManager()
    {
        $this->assertInstanceOf(EventManager::class, $this->app->getEventManager());
    }

    /**
     * @covers ::__construct
     * @covers ::bootstrap
     * @covers ::bootstrapPackages
     */
    public function testBootstrap()
    {
        $app = (new AppFactory)->create();
        $count = 0;
        $app->getEventManager()->on(App::EVENT_BOOTSTRAP, function () use (&$count) {
            $count++;
        });

        $app->bootstrap();
        $this->assertSame(1, $count);

        $app->bootstrap();
        $this->assertSame(1, $count);
    }

    /**
     * @covers ::dispatch
     * @covers ::tryFire
     */
    public function testDispatch()
    {
        $dispatch = false;
        $this->app->getEventManager()->on(App::EVENT_DISPATCH, function () use (&$dispatch) {
            $dispatch = true;
        });

        $this->app->dispatch();
        $this->assertTrue($dispatch);
    }

    /**
     * @covers ::dispatch
     * @covers ::tryFire
     */
    public function testDispatchCatchesExceptions()
    {
        $this->app->getEventManager()->on(App::EVENT_DISPATCH, function () {
            throw new \RuntimeException();
        });

        $this->app->route();
        $this->app->dispatch();

        $this->assertNotNull($this->app->getLifecycleEvent()->getException());
    }

    /**
     * @covers ::render
     * @covers ::tryFire
     */
    public function testRender()
    {
        $render = false;
        $this->app->getEventManager()->on(App::EVENT_RENDER, function () use (&$render) {
            $render = true;
        });

        $this->app->render();

        $this->assertTrue($render);
    }

    /**
     * @covers ::render
     * @covers ::tryFire
     */
    public function testRenderCatchesExceptions()
    {
        $this->app->getEventManager()->on(App::EVENT_RENDER, function () {
            throw new \RuntimeException();
        });

        $this->app->route();
        $this->app->render();

        $this->assertNotNull($this->app->getLifecycleEvent()->getException());
    }

    /**
     * @covers ::__construct
     * @covers ::route
     */
    public function testRoute()
    {
        $error = false;
        $this->app->getEventManager()->on(App::EVENT_ROUTE, function (LifecycleEvent $event) use (&$route) {
            $event->setRouteMatch(new RouteMatch(new Route('/', 'handler')));
        });
        $this->app->getEventManager()->on(App::EVENT_ROUTE_ERROR, function () use (&$error) {
            $error = true;
        });

        $this->app->route($this->newRequest('/'));

        $this->assertFalse($error);
        $this->assertInstanceOf(RouteMatch::class, $this->app->getLifecycleEvent()->getRouteMatch());
    }

    /**
     * @covers ::route
     */
    public function testRouteError()
    {
        $error = false;
        $this->app->getEventManager()->on(App::EVENT_ROUTE_ERROR, function () use (&$error) {
            $error = true;
        });

        $this->app->route($this->newRequest('/'));

        $this->assertTrue($error);
    }

    /**
     * @covers ::isDebugEnabled
     */
    public function testIsDebugEnabled()
    {
        $this->assertFalse($this->app->isDebugEnabled());

        $app = (new AppFactory)->createWeb(['debug' => true]);
        $this->assertTrue($app->isDebugEnabled());
    }

    /**
     * @covers ::getConfig
     */
    public function testGetConfig()
    {
        $this->assertInstanceOf(AppConfig::class, $this->app->getConfig());
    }

    /**
     * @covers ::getPackageManager
     */
    public function testGetPackageManager()
    {
        $this->assertInstanceOf(PackageManager::class, $this->app->getPackageManager());
    }

    /**
     * @covers ::getServiceContainer
     */
    public function testGetServiceContainer()
    {
        $this->assertInstanceOf(Container::class, $this->app->getServiceContainer());
    }

    /**
     * @covers ::getRouter
     */
    public function testGetRouter()
    {
        $this->assertInstanceOf(Router::class, $this->app->getRouter());
    }

    /**
     * @covers ::getLifecycleEvent
     */
    public function testGetLifecycleEvent()
    {
        $this->assertNull($this->app->getLifecycleEvent());
        $this->app->route();
        $this->assertInstanceOf(LifecycleEvent::class, $this->app->getLifecycleEvent());
    }

    /**
     * @covers ::bootstrapEnvironment
     */
    public function testBootstrapEnvironment()
    {
        $app = (new AppFactory)->create(['environment' => ['TONIS_ENV_TEST' => 'bar']]);
        $app->bootstrap();

        $this->assertSame('bar', getenv('TONIS_ENV_TEST'));
    }

    /**
     * @covers ::bootstrapEnvironment
     * @expectedException \Tonis\Web\Exception\MissingRequiredEnvironmentException
     * @expectedExceptionMessage The environment variable "TONIS_ENV_TEST" is missing but is set as required
     */
    public function testMissingEnvironmentThrowsException()
    {
        $app = (new AppFactory)->create(['required_environment' => ['TONIS_ENV_TEST']]);
        $app->bootstrap();
    }

    protected function tearDown()
    {
        putenv('TONIS_ENV_TEST');
    }

    protected function setUp()
    {
        $this->app = (new AppFactory)->create(['packages' => [TestPackage::class]]);
        $this->app->bootstrap();
    }
}
