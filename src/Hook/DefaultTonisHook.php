<?php
namespace Tonis\Mvc\Hook;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\RequestInterface;
use Tonis\Di\Container;
use Tonis\Di\ContainerUtil;
use Tonis\Dispatcher\Dispatcher;
use Tonis\Mvc\Exception\InvalidDispatchResultException;
use Tonis\Mvc\Exception\InvalidTemplateException;
use Tonis\Mvc\Exception\InvalidViewModelException;
use Tonis\Mvc\Exception\MissingRequiredEnvironmentException;
use Tonis\Mvc\Package\PackageInterface;
use Tonis\Mvc\Tonis;
use Tonis\Router\RouteMatch;
use Tonis\View\ViewManager;
use Tonis\View\ViewModel;
use Tonis\View\ViewModelInterface;

final class DefaultTonisHook extends AbstractTonisHook
{
    /**
     * {@inheritDoc}
     */
    public function onBootstrap(Tonis $app, array $config)
    {
        $this->loadPackages($app, isset($config['packages']) ? $config['packages'] : []);
        $this->configurePackages($app);

        $config = $app->getPackageManager()->getMergedConfig();

        $this->validateEnvironment($config['tonis']['required_environment']);
        $this->configureViewManager($app->getDi(), $app->getViewManager(), $config['tonis']['view_manager']);
    }

    /**
     * {@inheritDoc}
     */
    public function onRoute(Tonis $app, RequestInterface $request)
    {
        $app->getRouteCollection()->match($request);
    }

    /**
     * {@inheritDoc}
     */
    public function onRouteError(Tonis $app, RequestInterface $request)
    {
        $model = new ViewModel([
            'path' => $request->getUri()->getPath(),
            'type' => 'route'
        ]);

        $model->setTemplate($app->getViewManager()->getNotFoundTemplate());
        $app->setDispatchResult($model);
    }


    /**
     * {@inheritDoc}
     */
    public function onDispatch(Tonis $app, RouteMatch $match = null)
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
            } elseif (is_string($result)) {
                $result = new ViewModel(['content' => $result]);
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
    public function onDispatchInvalidResult(Tonis $app, InvalidDispatchResultException $ex, RequestInterface $request)
    {
        $model = new ViewModel([
            'exception' => $ex,
            'type' => 'invalid-dispatch-result',
            'path' => $request->getUri()->getPath()
        ]);
        $model->setTemplate('@error/error');
        $app->setDispatchResult($model);
    }

    /**
     * {@inheritDoc}
     */
    public function onDispatchException(Tonis $app, Exception $ex)
    {
        $model = new ViewModel([
            'exception' => $ex,
            'type' => 'exception'
        ]);
        $model->setTemplate('@error/error');
        $app->setDispatchResult($model);
    }

    /**
     * {@inheritDoc}
     */
    public function onRender(Tonis $app, ViewManager $vm)
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
     * @param Tonis $app
     * @param array $packages
     */
    private function loadPackages(Tonis $app, array $packages)
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
     * @param Tonis $app
     */
    private function configurePackages(Tonis $app)
    {
        $pm = $app->getPackageManager();

        foreach ($pm->getPackages() as $package) {
            if ($package instanceof PackageInterface) {
                $package->configureDi($app->getDi());
                $package->configureRoutes($app->getRouteCollection());
                $package->bootstrap($app);
            }
        }
    }

    /**
     * @param Container $di
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