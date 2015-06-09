<?php
namespace Tonis\Mvc;

use Interop\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Tonis\Event\EventManager;
use Tonis\Mvc\Package\PackageInterface;
use Tonis\Package\PackageManager;
use Tonis\Router\RouteCollection;
use Tonis\Router\RouteMatch;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequestFactory;

final class Tonis
{
    const EVENT_BOOTSTRAP = 'bootstrap';
    const EVENT_DISPATCH = 'dispatch';
    const EVENT_DISPATCH_EXCEPTION = 'dispatch.exception';
    const EVENT_RENDER = 'render';
    const EVENT_RENDER_EXCEPTION = 'render.exception';
    const EVENT_ROUTE = 'route';
    const EVENT_ROUTE_ERROR = 'route.error';
    const EVENT_RESPOND = 'respond';

    /** @var bool */
    private $bootstrapped = false;
    /** @var ContainerInterface */
    private $di;
    /** @var TonisConfig */
    private $config;
    /** @var EventManager */
    private $events;
    /** @var PackageManager */
    private $packageManager;
    /** @var RouteCollection */
    private $routes;
    /** @var LifecycleEvent */
    private $lifecycleEvent;

    public function __construct(
        TonisConfig $config,
        ContainerInterface $di,
        EventManager $events,
        PackageManager $packageManager,
        RouteCollection $routes
    ) {
        $this->config = $config;
        $this->di = $di;
        $this->events = $events;
        $this->packageManager = $packageManager;
        $this->routes = $routes;
    }

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param callable $next
     * @return ResponseInterface|Response
     */
    public function __invoke(
        RequestInterface $request = null,
        ResponseInterface $response = null,
        callable $next = null
    ) {
        return $this->run($request, $response);
    }

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface|Response
     */
    public function run(RequestInterface $request = null, ResponseInterface $response = null)
    {
        if (null !== $response) {
            $this->lifecycleEvent->setResponse($response);
        }

        $this->bootstrap($request);
        $this->route();
        $this->dispatch();
        $this->render();
        $this->respond();

        return $this->lifecycleEvent->getResponse();
    }

    /**
     * @param RequestInterface $request
     */
    public function bootstrap(RequestInterface $request = null)
    {
        if ($this->bootstrapped) {
            return;
        }

        $this->lifecycleEvent = new LifecycleEvent($request ?: ServerRequestFactory::fromGlobals());

        $pm = $this->packageManager;
        $pm->add(TonisPackage::class);
        foreach ($this->config->getPackages() as $package) {
            $pm->add($package);
        }
        $pm->load();

        foreach ($pm->getPackages() as $package) {
            if ($package instanceof PackageInterface) {
                $package->configureServices($this->di);
                $package->bootstrap($this);
                $package->configureRoutes($this->routes);
            }
        }

        $this->events->fire(self::EVENT_BOOTSTRAP, $this->lifecycleEvent);
        $this->bootstrapped = true;
    }

    public function route()
    {
        $this->events()->fire(self::EVENT_ROUTE, $this->lifecycleEvent);

        if (!$this->lifecycleEvent->getRouteMatch() instanceof RouteMatch) {
            $this->events()->fire(self::EVENT_ROUTE_ERROR, $this->lifecycleEvent);
        }
    }

    public function dispatch()
    {
        $this->tryFire(self::EVENT_DISPATCH, self::EVENT_DISPATCH_EXCEPTION);
    }

    public function render()
    {
        $this->tryFire(self::EVENT_RENDER, self::EVENT_RENDER_EXCEPTION);
    }

    public function respond()
    {
        $this->events()->fire(self::EVENT_RESPOND, $this->lifecycleEvent);
    }

    /**
     * @return bool
     */
    public function isDebugEnabled()
    {
        return $this->config->isDebugEnabled();
    }

    /**
     * @return ContainerInterface
     */
    public function di()
    {
        return $this->di;
    }

    /**
     * @return EventManager
     */
    public function events()
    {
        return $this->events;
    }

    /**
     * @return RouteCollection
     */
    public function routes()
    {
        return $this->routes;
    }

    /**
     * @return PackageManager
     */
    public function getPackageManager()
    {
        return $this->packageManager;
    }

    /**
     * @return TonisConfig
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return LifecycleEvent
     */
    public function getLifecycleEvent()
    {
        return $this->lifecycleEvent;
    }

    /**
     * @param string $event
     * @param string $exceptionEvent
     */
    private function tryFire($event, $exceptionEvent)
    {
        try {
            $this->events()->fire($event, $this->lifecycleEvent);
        } catch (\Exception $ex) {
            $this->lifecycleEvent->setException($ex);
            $this->events()->fire($exceptionEvent, $this->lifecycleEvent);
        }
    }
}
