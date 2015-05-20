<?php
namespace Tonis\Mvc\Hook;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\RequestInterface;
use Tonis\Di\Container;
use Tonis\Di\ContainerUtil;
use Tonis\Dispatcher\Dispatcher;
use Tonis\Mvc\App;
use Tonis\Mvc\Exception\InvalidDispatchResultException;
use Tonis\Mvc\Exception\InvalidTemplateException;
use Tonis\Mvc\Exception\InvalidViewModelException;
use Tonis\Mvc\Exception\MissingRequiredEnvironmentException;
use Tonis\Mvc\Package\PackageInterface;
use Tonis\Router\RouteMatch;
use Tonis\View\ViewManager;
use Tonis\View\ViewModel;
use Tonis\View\ViewModelInterface;

final class DefaultMvcHook extends AbstractAppHook
{
    /**
     * {@inheritDoc}
     */
    public function onBootstrap(App $app, array $config)
    {
        $this->loadPackages($app, $config['packages']);
        $this->configurePackages($app);
        $this->validateEnvironment($app->getPackageManager()->getMergedConfig()['tonis']['required_environment']);

        $packageConfig = $app->getPackageManager()->getMergedConfig();
        $this->configureViewManager($app->getDi(), $app->getViewManager(), $packageConfig['tonis']['view_manager']);
    }

    /**
     * {@inheritDoc}
     */
    public function onRoute(App $app, RequestInterface $request)
    {
        $app->getRouteCollection()->match($request);
    }

    /**
     * {@inheritDoc}
     */
    public function onRouteError(App $app, RequestInterface $request)
    {
        $model = new ViewModel([
            'path' => $request->getUri()->getPath(),
            'type' => 'route'
        ]);

        $model->setTemplate('error/404');
        $app->setDispatchResult($model);
    }


    /**
     * {@inheritDoc}
     */
    public function onDispatch(App $app, RouteMatch $match = null)
    {
        if (null !== $app->getDispatchResult()) {
            return;
        }
        if (!$match instanceof RouteMatch) {
            return;
        }

        $handler = $match->getRoute()->getHandler();
        $dispatcher = new Dispatcher();

        try {
            $result = $dispatcher->dispatch($handler, $match->getParams());

            if ($result === $handler) {
                $result = new InvalidDispatchResultException();
            } elseif ($result === null) {
                $result = new ViewModel();
            } elseif (is_array($result)) {
                $result = new ViewModel($result);
            } elseif (!$result instanceof ViewModelInterface) {
                $result = new InvalidDispatchResultException('Failed to dispatch; invalid dispatch result');
            }

            $app->setDispatchResult($result);
        } catch (\Exception $ex) {
            $app->setDispatchResult($ex);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function onDispatchInvalidResult(App $app, InvalidDispatchResultException $ex, RequestInterface $request)
    {
        $model = new ViewModel([
            'exception' => $ex,
            'type' => 'invalid-result',
            'path' => $request->getUri()->getPath()
        ]);
        $model->setTemplate('error/error');
        $app->setDispatchResult($model);
    }

    /**
     * {@inheritDoc}
     */
    public function onDispatchException(App $app, Exception $ex)
    {
        $model = new ViewModel([
            'exception' => $ex,
            'type' => 'exception'
        ]);
        $model->setTemplate('error/error');
        $app->setDispatchResult($model);
    }

    /**
     * {@inheritDoc}
     */
    public function onRender(App $app, ViewManager $vm)
    {
        $model = $app->getDispatchResult();

        if ($model instanceof ResponseInterface) {
            return;
        }

        if (!$model instanceof ViewModelInterface) {
            $app->setRenderResult(new InvalidViewModelException());
            return;
        }

        if (!$model->getTemplate()) {
            $match = $app->getRouteCollection()->getLastMatch();
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

                $model->setTemplate($template);
            } else {
                $app->setRenderResult(new InvalidTemplateException('No template was available for rendering'));
                return;
            }
        }

        try {
            $app->setRenderResult($vm->render($model));
        } catch (\Exception $ex) {
            echo $ex->getMessage();exit;
        }
    }

    /**
     * @param App $app
     * @param array $packages
     */
    private function loadPackages(App $app, array $packages)
    {
        $pm = $app->getPackageManager();

        $pm->add('Tonis\\Mvc');
        foreach ($packages as $package) {
            if ($package[0] == '?') {
                if (!$app->isDebugEnabled()) {
                    continue;
                }
                $package = substr($package, 1);
            }
            $pm->add($package);
        }
        $pm->load();

        $config = $pm->getMergedConfig();
        foreach ($config as $key => $value) {
            $app->getDi()[$key] = $value;
        }
    }

    /**
     * @param App $app
     */
    private function configurePackages(App $app)
    {
        $pm = $app->getPackageManager();

        foreach ($pm->getPackages() as $package) {
            if ($package instanceof PackageInterface) {
                $package->configureRoutes($app->getRouteCollection());
                $package->configureDi($app->getDi());
            }
        }
    }

    /**
     * @param ViewManager $vm
     * @param array $config
     */
    private function configureViewManager(Container $di, ViewManager $vm, array $config)
    {
        $vm->setFallbackStrategy(ContainerUtil::get($di, $config['fallback_strategy']));

        foreach ($config['strategies'] as $strategy) {
            if (empty($strategy)) {
                continue;
            }

            $vm->addStrategy(ContainerUtil::get($di, $strategy));
        }

        $vm->setErrorTemplate($config['error_template']);
        $vm->setNotFoundTemplate($config['not_found_template']);
    }

    /**
     * @param array $required
     */
    private function validateEnvironment(array $required)
    {
        foreach ($required as $name) {
            if (empty($name)) {
                continue;
            }

            if (false === getenv($name)) {
                throw new MissingRequiredEnvironmentException(
                    sprintf(
                        'Environment variable "%s" is required and missing',
                        $name
                    )
                );
            }
        }
    }
}
