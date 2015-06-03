<?php
namespace Tonis\Mvc;

use Psr\Http\Message;
use Tonis\Di;
use Tonis\Dispatcher;
use Tonis\Hookline;
use Tonis\Mvc;
use Tonis\Package;
use Tonis\Router;
use Tonis\View;
use Zend\Diactoros;

final class Tonis implements Hookline\HooksAwareInterface
{
    use Hookline\HooksAwareTrait;

    /** @var bool */
    private $debug = true;
    /** @var bool */
    private $loaded = false;
    /** @var Di\Container */
    private $di;
    /** @var Package\Manager */
    private $packageManager;
    /** @var Router\Collection */
    private $routes;
    /** @var View\Manager */
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
        $this->init($config);
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
     */
    public function bootstrap(Message\RequestInterface $request = null, Message\ResponseInterface $response = null)
    {
        if ($this->loaded) {
            return;
        }

        $this->request = $request ? $request : Diactoros\ServerRequestFactory::fromGlobals();
        $this->response = $response ? $response : new Diactoros\Response();

        $this->hooks()->run('onBootstrap');
        $this->loaded = true;
    }

    /**
     * @param Message\RequestInterface $request
     */
    public function route(Message\RequestInterface $request)
    {
        $this->hooks()->run('onRoute', $request);
        $match = $this->getRouteCollection()->match($request);

        if (!$match instanceof Router\Match) {
            $this->hooks()->run('onRouteError', $request);
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
        $this->hooks()->run('onDispatch', $this->routes->getLastMatch());

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
        $this->hooks()->run('onRender', $this->viewManager);

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
     * @return Di\Container
     */
    public function getDi()
    {
        return $this->di;
    }

    /**
     * @return View\Manager
     */
    public function getViewManager()
    {
        return $this->viewManager;
    }

    /**
     * @return Package\Manager
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
     * @todo Make the services customizable for replacement options
     */
    private function init(array $config)
    {
        $this->debug = isset($config['debug']) ? (bool) $config['debug'] : true;

        if (!isset($config['environment'])) {
            $config['environment'] = [];
        }
        if (!isset($config['required_environment'])) {
            $config['required_environment'] = [];
        }
        if (!isset($config['packages'])) {
            $config['packages'] = [];
        }

        $this->di = new Di\Container;
        $this->di->set(self::class, $this);
        $this->di->set(Router\Collection::class, new Router\Collection);
        $this->di->set(Package\Manager::class, new Factory\PackageManagerFactory($this, $config['packages']));
        $this->di->set(Hookline\Container::class, new Factory\HooklineContainerFactory(isset($config['hooks']) ? $config['hooks'] : []));
        $this->di->set(View\Manager::class, Mvc\Factory\ViewManagerFactory::class);

        $this->routes = $this->di->get(Router\Collection::class);
        $this->packageManager = $this->di->get(Package\Manager::class);
        $this->hooks = $this->di->get(Hookline\Container::class);
        $this->viewManager = $this->di->get(View\Manager::class);

        $this->initEnvironment($config['environment'], $config['required_environment']);
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

            if ($result instanceof Di\ServiceFactoryInterface) {
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
            $this->hooks()->run('onDispatchException', $this->dispatchResult);
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
