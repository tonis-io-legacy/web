<?php
namespace Tonis\Mvc;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Tonis\Di\Container;
use Tonis\Dispatcher\Dispatcher;
use Tonis\Event\EventManager;
use Tonis\Mvc\Subscriber\BootstrapSubscriber;
use Tonis\Mvc\Subscriber\DispatchSubscriber;
use Tonis\Mvc\Subscriber\RenderSubscriber;
use Tonis\Mvc\Subscriber\RouteSubscriber;
use Tonis\Package\PackageManager;
use Tonis\Router\RouteCollection;
use Tonis\Router\RouteMatch;
use Tonis\View\ViewManager;
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

    /** @var TonisConfig */
    private $config;
    /** @var Container */
    private $di;
    /** @var LifecycleEvent */
    private $lifecycleEvent;
    /** @var RequestInterface */
    private $request;

    /**
     * @param array $config
     * @return Tonis
     */
    public static function createWithDefaults(array $config = [])
    {
        $tonis = new self($config);

        $di = $tonis->di();

        $di->set(Dispatcher::class, new Dispatcher);
        $di->set(PackageManager::class, new PackageManager);
        $di->set(ViewManager::class, new ViewManager);

        $di->set(BootstrapSubscriber::class, new BootstrapSubscriber($di->get(PackageManager::class)));
        $di->set(DispatchSubscriber::class, new DispatchSubscriber($di->get(Dispatcher::class)));
        $di->set(RenderSubscriber::class, new RenderSubscriber($di->get(ViewManager::class)));
        $di->set(RouteSubscriber::class, new RouteSubscriber($tonis->routes()));

        return $tonis;
    }

    /**
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = new TonisConfig($config);

        $this->di = new Container;
        $this->events = new EventManager;
        $this->routes = new RouteCollection;
    }

    /**
     * @param RequestInterface $request
     */
    public function bootstrap(RequestInterface $request = null)
    {
        $this->request = $request ?: ServerRequestFactory::fromGlobals();
        $this->lifecycleEvent = new LifecycleEvent($this->request);

        $this->events()->fire(self::EVENT_BOOTSTRAP, $this->lifecycleEvent);
    }

    public function route()
    {
        if ($this->lifecycleEvent->getResponse()) {
            return;
        }
        $this->events()->fire(self::EVENT_ROUTE, $this->lifecycleEvent);

        if (!$this->lifecycleEvent->getRouteMatch() instanceof RouteMatch) {
            $this->events()->fire(self::EVENT_ROUTE_ERROR, $this->lifecycleEvent);
        }
    }

    public function dispatch()
    {
        if ($this->lifecycleEvent->getResponse()) {
            return;
        }
        try {
            $this->events()->fire(self::EVENT_DISPATCH, $this->lifecycleEvent);
        } catch (\Exception $ex) {
            $this->lifecycleEvent->setException($ex);
            $this->events()->fire(self::EVENT_DISPATCH_EXCEPTION, $this->lifecycleEvent);
        }
    }

    public function render()
    {
        if ($this->lifecycleEvent->getResponse()) {
            return;
        }
        try {
            $this->events()->fire(self::EVENT_RENDER, $this->lifecycleEvent);
        } catch (\Exception $ex) {
            $this->lifecycleEvent->setException($ex);
            $this->events()->fire(self::EVENT_RENDER_EXCEPTION, $this->lifecycleEvent);
        }
    }

    /**
     * @return bool
     */
    public function isDebugEnabled()
    {
        return $this->config->isDebugEnabled();
    }

    /**
     * @return EventManager
     */
    public function events()
    {
        return $this->events;
    }

    /**
     * @return Container
     */
    public function di()
    {
        return $this->di;
    }

    /**
     * @return RouteCollection
     */
    public function routes()
    {
        return $this->routes;
    }

    /**
     * @return LifecycleEvent
     */
    public function getLifecycleEvent()
    {
        return $this->lifecycleEvent;
    }

//    /**
//     * @param array $config
//     */
//    private function bootstrap2(array $config)
//    {
//        foreach (['environment', 'packages', 'required_environment'] as $key) {
//            if (!isset($config[$key])) {
//                $config[$key] = [];
//            }
//        }
//
//        foreach ($config['required_environment'] as $key) {
//            if (!isset($config['environment'][$key])) {
//                throw new Exception\MissingRequiredEnvironmentException(
//                    sprintf(
//                        'Environment variable "%s" is required and missing',
//                        $key
//                    )
//                );
//            }
//        }
//
//        foreach ($config['environment'] as $key => $value) {
//            putenv($key . '=' . $value);
//        }
//
//        putenv('TONIS_DEBUG=' . $this->isDebugEnabled());
//
//        $this->events = (new Factory\EventManagerFactory)->createService($di);
//        $this->packageManager = (new Factory\PackageManagerFactory($debug, $config['packages']))->createService($di);
//
//        foreach ($this->packageManager->getPackages() as $package) {
//            if ($package instanceof Mvc\Package\PackageInterface) {
//                $package->configureDi($di);
//                $package->configureRoutes($this->routes);
//                $package->bootstrap($this);
//
//                $di[$package->getName()] = $package->getConfig();
//            }
//        }
//
//        $this->events()->fire(self::EVENT_BOOTSTRAP);
//
//        $this->viewManager = (new Factory\ViewManagerFactory)->createService($di);
//    }
}
