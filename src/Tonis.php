<?php
namespace Tonis\Mvc;

use Psr\Http\Message\RequestInterface;
use Tonis\Di\Container;
use Tonis\Di\ContainerUtil;
use Tonis\Dispatcher\Dispatcher;
use Tonis\Event\EventManager;
use Tonis\Mvc\Factory\PackageManagerFactory;
use Tonis\Mvc\Factory\ViewManagerFactory;
use Tonis\Mvc\Subscriber\BootstrapSubscriber;
use Tonis\Mvc\Subscriber\DispatchSubscriber;
use Tonis\Mvc\Subscriber\RenderSubscriber;
use Tonis\Mvc\Subscriber\RouteSubscriber;
use Tonis\Package\PackageManager;
use Tonis\Router\RouteCollection;
use Tonis\Router\RouteMatch;
use Tonis\View\ViewManager;
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

    /** @var TonisConfig */
    private $config;
    /** @var Container */
    private $di;
    /** @var LifecycleEvent */
    private $lifecycleEvent;
    /** @var RouteCollection */
    private $routes;
    /** @var ViewManager */
    private $viewManager;

    /**
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = new TonisConfig($config);

        $this->di = new Container;
        $this->dispatcher = new Dispatcher;
        $this->events = new EventManager;
        $this->routes = new RouteCollection;
        $this->packageManager = new PackageManager(['cache_dir' => $this->config->getPackageCacheDir()]);
        $this->viewManager = new ViewManager;

        $this->di->set(PackageManager::class, $this->packageManager);
    }

    /**
     * @param RequestInterface $request
     */
    public function bootstrap(RequestInterface $request = null)
    {
        $this->lifecycleEvent = new LifecycleEvent($this, $request ?: ServerRequestFactory::fromGlobals());

        foreach ($this->config->getSubscribers() as $subscriber) {
            $this->events()->subscribe(ContainerUtil::get($this->di, $subscriber));
        }

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

    public function respond()
    {
        $response = $this->lifecycleEvent->getResponse() ? $this->lifecycleEvent->getResponse() : new Response;
        $response->getBody()->write($this->lifecycleEvent->getRenderResult());

        echo $response->getBody();
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
     * @return ViewManager
     */
    public function getViewManager()
    {
        return $this->viewManager;
    }

    /**
     * @return PackageManager
     */
    public function getPackageManager()
    {
        return $this->packageManager;
    }

    /**
     * @return Dispatcher
     */
    public function getDispatcher()
    {
        return $this->dispatcher;
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
}
