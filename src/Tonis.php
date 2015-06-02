<?php
namespace Tonis\Mvc;

use Psr\Http\Message;
use Tonis\Di;
use Tonis\Hookline;
use Tonis\Mvc;
use Tonis\PackageManager;
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
    /** @var PackageManager\Manager */
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

        if ($this->dispatchResult instanceof Exception\InvalidDispatchResultException) {
            $this->hooks()->run('onDispatchInvalidResult', $this->dispatchResult, $request);
        } elseif ($this->dispatchResult instanceof \Exception) {
            $this->hooks()->run('onDispatchException', $this->dispatchResult);
        }
    }

    public function render()
    {
        $this->hooks()->run('onRender', $this->viewManager);
        if ($this->renderResult instanceof \Exception) {
            $this->hooks()->run('onRenderException', $this->renderResult);
        }
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
     * @return PackageManager\Manager
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
    private function init(array $config)
    {
        if (isset($config['debug'])) {
            $this->debug = (bool) $config['debug'];
        }
        if (!isset($config['environment'])) {
            $config['environment'] = [];
        }
        if (!isset($config['required_environment'])) {
            $config['required_environment'] = [];
        }
        if (!isset($config['hooks'])) {
            $config['hooks'] = [new Hook\DefaultTonisHook($this)];
        }
        if (!isset($config['packages'])) {
            $config['packages'] = [];
        }

        $this->initHooks($config['hooks']);
        $this->initDi();
        $this->initPackages($config['packages']);
        $this->initEnvironment($config['environment'], $config['required_environment']);
        $this->initViewManager();
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
     * @param array $hooks
     * @throws Hookline\Exception\InvalidHookException
     */
    private function initHooks(array $hooks)
    {
        $this->hooks = new Hookline\Container(Hook\TonisHookInterface::class);

        foreach ($hooks as $hook) {
            if (is_string($hook)) {
                if (!class_exists($hook)) {
                    throw new Hookline\Exception\InvalidHookException();
                }
                $hook = new $hook;
            }
            $this->hooks()->add($hook);
        }
    }

    private function initDi()
    {
        $this->di = new Di\Container();
    }

    private function initPackages(array $packages)
    {
        $pm = $this->packageManager = new PackageManager\Manager();
        $pm->add(__NAMESPACE__);

        foreach ($packages as $package) {
            if ($package[0] == '?') {
                if (!$this->debug) {
                    continue;
                }
                $package = substr($package, 1);
            }
            $pm->add($package);
        }
        $pm->load();

        $config = $pm->getMergedConfig();
        foreach ($config as $key => $value) {
            $this->getDi()[$key] = $value;
        }

        foreach ($this->packageManager->getPackages() as $package) {
            if ($package instanceof Package\PackageInterface) {
                $package->configureDi($this->getDi());
                $package->configureRoutes($this->getRouteCollection());
                $package->bootstrap($this);
            }
        }
    }

    private function initViewManager()
    {
        $vm = $this->viewManager;
        $config = $this->packageManager->getMergedConfig();
        $config = $config['tonis']['view_manager'];

        foreach ($config['strategies'] as $strategy) {
            if (empty($strategy)) {
                continue;
            }

            $vm->addStrategy(Di\ContainerUtil::get($this->getDi(), $strategy));
        }

        $vm->setErrorTemplate($config['error_template']);
        $vm->setNotFoundTemplate($config['not_found_template']);
    }
}
