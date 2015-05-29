<?php
namespace Tonis\Mvc;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Tonis\Di\Container;
use Tonis\Hookline\Exception\InvalidHookException;
use Tonis\Hookline\HookContainer;
use Tonis\Hookline\HooksAwareInterface;
use Tonis\Hookline\HooksAwareTrait;
use Tonis\Mvc\Exception;
use Tonis\Mvc\Hook\DefaultMvcHook;
use Tonis\Mvc\Hook\DefaultTonisHook;
use Tonis\Mvc\Hook\TonisHookInterface;
use Tonis\PackageManager\PackageManager;
use Tonis\Router\RouteCollection;
use Tonis\Router\RouteMatch;
use Tonis\View\ViewManager;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Stratigility\MiddlewareInterface;

final class Tonis implements HooksAwareInterface, MiddlewareInterface
{
    use HooksAwareTrait;

    /** @var bool */
    private $loaded = false;
    /** @var array */
    private $config;
    /** @var Container */
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
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->di = new Container();
        $this->packageManager = new PackageManager();
        $this->routes = new RouteCollection();
        $this->viewManager = new ViewManager();

        $this->di->set(self::class, $this);

        $this->prepareEnvironment(isset($config['environment']) ? $config['environment'] : []);
        $this->initHooks(isset($config['hooks']) ? $config['hooks'] : []);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable $next
     * @return ResponseInterface
     * @throws \Exception if render result is not renderable and $next is null
     * @throws string
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        return $response;
    }

    /**
     * @param RequestInterface|null $request
     * @param ResponseInterface|null $response
     */
    public function run(RequestInterface $request = null, ResponseInterface $response = null)
    {
        $this->bootstrap($request, $response);
        $this->route($this->request);
        $this->dispatch($this->request);
        $this->render($this->request, $this->response);
        $this->respond($this->response);
    }

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     */
    public function bootstrap(RequestInterface $request = null, ResponseInterface $response = null)
    {
        if ($this->loaded) {
            return;
        }

        $this->request = $request ? $request : ServerRequestFactory::fromGlobals();
        $this->response = $response ? $response : new Response();

        $this->hooks()->run('onBootstrap', $this, $this->config);
        $this->loaded = true;
    }

    public function route(RequestInterface $request)
    {
        $this->hooks()->run('onRoute', $this, $request);

        if (!$this->routes->getLastMatch() instanceof RouteMatch) {
            $this->hooks()->run('onRouteError', $this, $request);
        }
    }

    public function dispatch(RequestInterface $request)
    {
        $this->hooks()->run('onDispatch', $this, $this->routes->getLastMatch());

        if ($this->dispatchResult instanceof Exception\InvalidDispatchResultException) {
            $this->hooks()->run('onDispatchInvalidResult', $this, $this->dispatchResult, $request);
        } elseif ($this->dispatchResult instanceof \Exception) {
            $this->hooks()->run('onDispatchException', $this, $this->dispatchResult);
        }
    }

    public function render()
    {
        $this->hooks()->run('onRender', $this, $this->viewManager);
        if ($this->renderResult instanceof \Exception) {
            $this->hooks()->run('onRenderException', $this, $this->renderResult);
        }
    }

    public function respond(ResponseInterface $response)
    {
        $response->getBody()->write($this->renderResult);
        echo $response->getBody();
    }

    /**
     * @return bool
     */
    public function isDebugEnabled()
    {
        return getenv('TONIS_DEBUG');
    }

    /**
     * @return Container
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
        $this->hooks = new HookContainer(TonisHookInterface::class);

        if (empty($hooks)) {
            $hooks[] = new DefaultTonisHook();
        }

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
