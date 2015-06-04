<?php
namespace Tonis\Mvc;

use Psr\Http\Message;
use Tonis\Di\Container;
use Tonis\Di\ServiceFactoryInterface;
use Tonis\Dispatcher;
use Tonis\Event\EventsAwareTrait;
use Tonis\Mvc;
use Tonis\Package\PackageManager;
use Tonis\Router;
use Tonis\View;
use Tonis\View\ViewManager;
use Zend\Diactoros;

final class Tonis
{
    use EventsAwareTrait;
    
    /** @var bool */
    private $debug = true;
    /** @var bool */
    private $loaded = false;
    /** @var Container */
    private $di;
    /** @var PackageManager */
    private $packageManager;
    /** @var Router\Collection */
    private $routes;
    /** @var ViewManager */
    private $viewManager;
    /** @var mixed */
    private $dispatchResult;
    /** @var string */
    private $renderResult;
    /** @var Message\RequestInterface */
    private $request;
    /** @var Message\ResponseInterface */
    private $response;

    /**
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->debug = isset($config['debug']) ? (bool) $config['debug'] : true;

        foreach(['environment', 'packages', 'required_environment'] as $key) {
            if (!isset($config[$key])) {
                $config[$key] = [];
            }
        }

        $this->initServices($config);
        $this->initEnvironment($config['environment'], $config['required_environment']);
    }

    /**
     * @param Message\RequestInterface|null $request
     * @param Message\ResponseInterface|null $response
     */
    public function run(Message\RequestInterface $request = null, Message\ResponseInterface $response = null)
    {
        $this->bootstrap($request, $response);
        $this->route($this->request);
        $this->dispatch($this->request);
        $this->render($this->request, $this->response);
        $this->respond($this->response);
    }

    /**
     * @param Message\RequestInterface $request
     * @param Message\ResponseInterface $response
     * @todo Make the services customizable for replacement options
     */
    public function bootstrap(Message\RequestInterface $request = null, Message\ResponseInterface $response = null)
    {
        if ($this->loaded) {
            return;
        }

        $this->request = $request ? $request : Diactoros\ServerRequestFactory::fromGlobals();
        $this->response = $response ? $response : new Diactoros\Response();

        $config = $this->packageManager->getMergedConfig();
        foreach ($config as $key => $value) {
            $di[$key] = $value;
        }

        foreach ($this->packageManager->getPackages() as $package) {
            if ($package instanceof Mvc\Package\PackageInterface) {
                $package->configureDi($this->di);
                $package->configureRoutes($this->routes);
                $package->bootstrap($this);
            }
        }

        $this->events()->fire('onBootstrap');
        $this->loaded = true;
    }

    /**
     * @param Message\RequestInterface $request
     */
    public function route(Message\RequestInterface $request)
    {
        $this->events()->fire('onRoute', $request);
        $match = $this->getRouteCollection()->match($request);

        if (!$match instanceof Router\Match) {
            $this->events()->fire('onRouteError', $request);
            $model = new View\Model\ViewModel(
                $this->viewManager->getNotFoundTemplate(),
                [
                    'path' => $request->getUri()->getPath(),
                    'type' => 'route'
                ]
            );

            $this->setDispatchResult($model);
        }
    }

    /**
     * @param Message\RequestInterface $request
     */
    public function dispatch(Message\RequestInterface $request)
    {
        $this->events()->fire('onDispatch', $this->routes->getLastMatch());

        if (null !== $this->dispatchResult) {
            return;
        }

        $routeMatch = $this->routes->getLastMatch();
        if (!$routeMatch instanceof Router\Match) {
            return;
        }

        $this->dispatchResult = $this->doDispatch($routeMatch);
    }

    public function render()
    {
        $this->events()->fire('onRender', $this->viewManager);

        if ($this->dispatchResult instanceof Message\ResponseInterface) {
            return;
        }

        $this->renderResult = $this->doRender($this->dispatchResult);
    }

    /**
     * @param Message\ResponseInterface $response
     */
    public function respond(Message\ResponseInterface $response)
    {
        $response->getBody()->write($this->renderResult);
        echo $response->getBody();
    }

    /**
     * @return bool
     */
    public function isDebugEnabled()
    {
        return $this->debug;
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
     * @return Router\Collection
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
     * @return Message\RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return Message\ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param array $config
     */
    private function initServices(array $config)
    {
        $this->di = new Container;
        $this->routes = new Router\Collection;
        $this->events = (new Factory\EventManagerFactory)->createService($this->di);
        $this->packageManager = (new Factory\PackageManagerFactory($this->isDebugEnabled(), $config['packages']))->createService($this->di);
        $this->viewManager = (new Factory\ViewManagerFactory)->createService($this->di);
    }

    /**
     * @param array $environment
     * @param array $required
     * @throws Exception\MissingRequiredEnvironmentException
     */
    private function initEnvironment(array $environment, array $required)
    {
        foreach ($required as $key) {
            if (!isset($environment[$key])) {
                throw new Exception\MissingRequiredEnvironmentException(
                    sprintf(
                        'Environment variable "%s" is required and missing',
                        $key
                    )
                );
            }
        }

        foreach ($environment as $key => $value) {
            putenv($key . '=' . $value);
        }

        putenv('TONIS_DEBUG=' . $this->isDebugEnabled());
    }

    /**
     * @param Router\Match $routeMatch
     * @return mixed
     */
    private function doDispatch(Router\Match $routeMatch)
    {
        $handler = $routeMatch->getRoute()->getHandler();

        if (is_string($handler) && $this->di->has($handler)) {
            $handler = $this->di->get($handler);
        }

        $dispatcher = new Dispatcher\Dispatcher;

        try {
            $result = $dispatcher->dispatch($handler, $routeMatch->getParams());

            if ($result instanceof ServiceFactoryInterface) {
                $result = $dispatcher->dispatch($result->createService($this->di), $routeMatch->getParams());
            }

            if (is_array($result)) {
                return new View\Model\ViewModel($result);
            } elseif (is_string($result)) {
                return new View\Model\StringModel($result);
            } elseif (!$result instanceof View\ModelInterface) {
                return $this->getExceptionViewModel(
                    'invalid-dispatch-result',
                    new Exception\InvalidDispatchResultException(
                        'Failed to dispatch; invalid dispatch result'
                    )
                );
            }
        } catch (\Exception $ex) {
            $this->events()->fire('onDispatchException', $this->dispatchResult);
            return $ex;
        }
        return null;
    }

    private function doRender()
    {
        $dispatchResult = $this->dispatchResult;

        if ($dispatchResult instanceof View\Model\ViewModel && !$dispatchResult->getTemplate()) {
            $match = $this->getRouteCollection()->getLastMatch();
            $handler = $match->getRoute()->getHandler();

            if (is_array($handler)) {
                $handler = $handler[0];
            }

            if (is_object($handler)) {
                $handler = get_class($handler);
            }

            if (is_string($handler)) {
                $replace = function ($match) {
                    return $match[1] . '-' . $match[2];
                };
                $template = preg_replace('@Action$@', '', $handler);
                $template = preg_replace_callback('@([a-z])([A-Z])@', $replace, $template);
                $template = strtolower($template);
                $template = str_replace('\\', '/', $template);

                $dispatchResult = new View\Model\ViewModel($template, $dispatchResult->getVariables());
            } else {
                return $this->getExceptionViewModel(
                    'no-template-available',
                    new Exception\InvalidTemplateException('No template was available for rendering')
                );
            }
        }

        return $this->viewManager->render($dispatchResult);
    }

    /**
     * @param string $type
     * @param \Exception $ex
     * @return View\Model\ViewModel
     */
    private function getExceptionViewModel($type, \Exception $ex)
    {
        return new View\Model\ViewModel(
            $this->viewManager->getErrorTemplate(),
            [
                'exception' => $ex,
                'type' => $type,
                'path' => $this->request->getUri()->getPath()
            ]
        );
    }
}
