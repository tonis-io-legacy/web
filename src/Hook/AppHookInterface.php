<?php
namespace Tonis\Mvc\Hook;

use Psr\Http\Message\RequestInterface;
use Tonis\Hookline\HookInterface;
use Tonis\Mvc\App;
use Tonis\Mvc\Exception\InvalidDispatchResultException;
use Tonis\Router\RouteMatch;
use Tonis\View\ViewManager;

interface AppHookInterface extends HookInterface
{
    /**
     * @param App $app
     * @param array $config
     */
    public function onBootstrap(App $app, array $config);

    /**
     * @param App $app
     * @param RequestInterface $request
     */
    public function onRoute(App $app, RequestInterface $request);

    /**
     * @param App $app
     * @param RequestInterface $request
     */
    public function onRouteError(App $app, RequestInterface $request);

    /**
     * @param App $app
     * @param RouteMatch $match
     */
    public function onDispatch(App $app, RouteMatch $match = null);

    /**
     * @param App $app
     * @param InvalidDispatchResultException $ex
     * @param RequestInterface $request
     */
    public function onDispatchInvalidResult(App $app, InvalidDispatchResultException $ex, RequestInterface $request);

    /**
     * @param App $app
     * @param \Exception $ex
     */
    public function onDispatchException(App $app, \Exception $ex);

    /**
     * @param App $app
     * @param ViewManager $vm
     */
    public function onRender(App $app, ViewManager $vm);
}
