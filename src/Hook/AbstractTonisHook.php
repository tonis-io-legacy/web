<?php
namespace Tonis\Mvc\Hook;

use Psr\Http\Message\RequestInterface;
use Tonis\Mvc\Exception\InvalidDispatchResultException;
use Tonis\Mvc\Tonis;
use Tonis\Router\Match as RouteMatch;
use Tonis\View\Manager as ViewManager;

abstract class AbstractTonisHook implements TonisHookInterface
{
    /**
     * {@inheritDoc}
     */
    public function onBootstrap(Tonis $app, array $config)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function onRoute(Tonis $app, RequestInterface $request)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function onRouteError(Tonis $app, RequestInterface $request)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function onDispatch(Tonis $app, RouteMatch $match = null)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function onDispatchInvalidResult(Tonis $app, InvalidDispatchResultException $ex, RequestInterface $request)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function onDispatchException(Tonis $app, \Exception $ex)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function onRender(Tonis $app, ViewManager $vm)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function onRenderException(Tonis $app, \Exception $ex)
    {
    }
}
