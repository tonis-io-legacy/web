<?php
namespace Tonis\Mvc;

use Interop\Container\ContainerInterface;
use Phly\Conduit\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Tonis\Hookline\Exception\InvalidHookException;
use Tonis\Hookline\HooksAwareInterface;
use Tonis\Hookline\HooksAwareTrait;
use Tonis\Mvc\Exception;
use Tonis\PackageManager\PackageManager;
use Tonis\Router\RouteCollection;
use Tonis\Router\RouteMatch;
use Tonis\View\ViewManager;

final class App implements HooksAwareInterface, MiddlewareInterface
{
    use HooksAwareTrait;

    /** @var array */
    private $config;
    /** @var ContainerInterface */
    private $di;
    /** @var PackageManager */
    private $packageManager;
    /** @var RouteCollection */
    private $routes;
    /** @var ViewManager */
    private $viewManager;
    /** @var mixed */
    private $dispatchResult;
    /** @var string */
    private $renderResult;
    /** @var ServerRequestInterface */
    private $request;
    /** @var ResponseInterface */
    private $response;

    /**
     * @param ContainerInterface $di
     * @param array $config
     */
    public function __construct(ContainerInterface $di, array $config)
    {
        $this->config = $config;
        $this->di = $di;
        $this->packageManager = new PackageManager();
        $this->routes = new RouteCollection();
        $this->viewManager = new ViewManager();

        $this->prepareEnvironment(isset($config['environment']) ? $config['environment'] : []);
        $this->initHooks(isset($config['hooks']) ? $config['hooks'] : []);
    }

    /**
     * {@inheritDoc}
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        $this->request = $request;
        $this->response = $response;

        $this->hooks()->run('onBootstrap', $this, $this->config);
        $this->hooks()->run('onRoute', $this, $request);
        if (!$this->routes->getLastMatch() instanceof RouteMatch) {
            $this->hooks()->run('onRouteError', $this, $request);
        }

        $this->hooks()->run('onDispatch', $this, $this->routes->getLastMatch());
        if ($this->dispatchResult instanceof Exception\InvalidDispatchResultException) {
            $this->hooks()->run('onDispatchInvalidResult', $this, $this->dispatchResult, $request);
        } elseif ($this->dispatchResult instanceof \Exception) {
            $this->hooks()->run('onDispatchException', $this, $this->dispatchResult);
        }

        $this->hooks()->run('onRender', $this, $this->getViewManager());
        if ($this->renderResult instanceof \Exception) {
            return $next($request, $response, $this->renderResult);
        }

        return $this->getResponse()->getBody()->write($this->getRenderResult());
    }

    /**
     * @return bool
     */
    public function isDebugEnabled()
    {
        return getenv('TONIS_DEBUG');
    }

    /**
     * @return ContainerInterface
     */
    public function getDi()
    {
        return $this->di;
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
     * @return RouteCollection
     */
    public function getRouteCollection()
    {
        return $this->routes;
    }

    /**
     * @return mixed
     */
    public function getDispatchResult()
    {
        return $this->dispatchResult;
    }

    /**
     * @param mixed $dispatchResult
     */
    public function setDispatchResult($dispatchResult)
    {
        $this->dispatchResult = $dispatchResult;
    }

    /**
     * @return string
     */
    public function getRenderResult()
    {
        return $this->renderResult;
    }

    /**
     * @param string $renderResult
     */
    public function setRenderResult($renderResult)
    {
        $this->renderResult = $renderResult;
    }

    /**
     * @return ServerRequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param array $environment
     */
    private function prepareEnvironment(array $environment)
    {
        foreach ($environment as $key => $value) {
            putenv($key . '=' . $value);
        }

        if (false === getenv('TONIS_DEBUG')) {
            putenv('TONIS_DEBUG=false');
        }
    }

    /**
     * @param array $hooks
     */
    private function initHooks(array $hooks)
    {
        foreach ($hooks as $hook) {
            if (is_string($hook)) {
                if (!class_exists($hook)) {
                    throw new InvalidHookException();
                }
                $hook = new $hook;
            }
            $this->hooks()->add($hook);
        }
    }
}
