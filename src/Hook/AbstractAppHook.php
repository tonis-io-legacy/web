<?php
namespace Tonis\Mvc\Hook;

use Psr\Http\Message\RequestInterface;
use Tonis\Mvc\App;
use Tonis\Mvc\Exception\InvalidDispatchResultException;
use Tonis\Router\RouteMatch;
use Tonis\View\ViewManager;

abstract class AbstractAppHook implements AppHookInterface
{
    /**
     * {@inheritDoc}
     */
    public function onBootstrap(App $app, array $config)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function onRoute(App $app, RequestInterface $request)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function onRouteError(App $app, RequestInterface $request)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function onDispatch(App $app, RouteMatch $match = null)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function onDispatchInvalidResult(App $app, InvalidDispatchResultException $ex, RequestInterface $request)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function onDispatchException(App $app, \Exception $ex)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function onRender(App $app, ViewManager $vm)
    {
    }
}
