<?php
namespace Tonis\Web;

use Interop\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Tonis\Event\EventManager;
use Tonis\Web\Exception\MissingRequiredEnvironmentException;
use Tonis\Web\Package\PackageInterface;
use Tonis\Package\PackageManager;
use Tonis\Router\Router;
use Tonis\Router\RouteMatch;
use Zend\Diactoros\Response;
use Zend\Diactoros\Server;
use Zend\Diactoros\ServerRequestFactory;

final class App
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
    private $serviceContainer;
    /** @var AppConfig */
    private $config;
    /** @var EventManager */
    private $events;
    /** @var PackageManager */
    private $packageManager;
    /** @var Router */
    private $router;
    /** @var LifecycleEvent */
    private $lifecycleEvent;

    public function __construct(
        AppConfig $config,
        ContainerInterface $serviceContainer,
        EventManager $events,
        PackageManager $packageManager,
        Router $router
    ) {
        $this->config = $config;
        $this->serviceContainer = $serviceContainer;
        $this->events = $events;
        $this->packageManager = $packageManager;
        $this->router = $router;
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
        $this->bootstrap();
        $this->route($request);

        if (null !== $response) {
            $this->lifecycleEvent->setResponse($response);
        }

        $this->dispatch();
        $this->render();
        $this->respond();

        return $this->lifecycleEvent->getResponse();
    }

    public function run(Response\EmitterInterface $emitter = null)
    {
        $server = Server::createServer($this, $_SERVER, $_GET, $_POST, $_COOKIE, $_FILES);

        if ($emitter) {
            $server->setEmitter($emitter);
        }

        $server->listen();
    }

    public function bootstrap()
    {
        if ($this->bootstrapped) {
            return;
        }

        $this->bootstrapEnvironment();
        $this->bootstrapPackages();

        $this->events->fire(self::EVENT_BOOTSTRAP, $this->lifecycleEvent);
        $this->bootstrapped = true;
    }

    /**
     * @param RequestInterface $request
     */
    public function route(RequestInterface $request = null)
    {
        $this->lifecycleEvent = new LifecycleEvent($request ?: ServerRequestFactory::fromGlobals());

        $this->getEventManager()->fire(self::EVENT_ROUTE, $this->lifecycleEvent);

        if (!$this->lifecycleEvent->getRouteMatch() instanceof RouteMatch) {
            $this->getEventManager()->fire(self::EVENT_ROUTE_ERROR, $this->lifecycleEvent);
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
        $this->getEventManager()->fire(self::EVENT_RESPOND, $this->lifecycleEvent);
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
    public function getServiceContainer()
    {
        return $this->serviceContainer;
    }

    /**
     * @return EventManager
     */
    public function getEventManager()
    {
        return $this->events;
    }

    /**
     * @return Router
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * @return PackageManager
     */
    public function getPackageManager()
    {
        return $this->packageManager;
    }

    /**
     * @return AppConfig
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
            $this->getEventManager()->fire($event, $this->lifecycleEvent);
        } catch (\Exception $ex) {
            $this->lifecycleEvent->setException($ex);
            $this->getEventManager()->fire($exceptionEvent, $this->lifecycleEvent);
        }
    }

    private function bootstrapEnvironment()
    {
        putenv("TONIS_DEBUG={$this->isDebugEnabled()}");

        foreach ($this->config->getEnvironment() as $key => $value) {
            putenv("{$key}={$value}");
        }
        foreach ($this->config->getRequiredEnvironment() as $key) {
            if (false === getenv($key)) {
                throw new MissingRequiredEnvironmentException(
                    sprintf('The environment variable "%s" is missing but is set as required', $key)
                );
            }
        }
    }

    private function bootstrapPackages()
    {
        $pm = $this->packageManager;
        $pm->add(AppPackage::class);
        foreach ($this->config->getPackages() as $package) {
            $pm->add($package);
        }
        $pm->load();

        foreach ($pm->getPackages() as $package) {
            if ($package instanceof PackageInterface) {
                $package->configureServices($this->serviceContainer);
                $package->bootstrap($this);
                $package->configureRoutes($this->router);
            }
        }
    }
}
