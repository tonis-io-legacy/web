<?php
namespace Tonis\Mvc\Hook;

use Psr\Http\Message\RequestInterface;
use Tonis\Mvc\Exception\InvalidDispatchResultException;
use Tonis\Router\Match as RouteMatch;

abstract class AbstractTonisHook implements TonisHookInterface
{
    /**
     * {@inheritDoc}
     */
    public function onBootstrap()
    {
    }

    /**
     * {@inheritDoc}
     */
    public function onRoute(RequestInterface $request)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function onRouteError(RequestInterface $request)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function onDispatch(RouteMatch $match = null)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function onDispatchInvalidResult(InvalidDispatchResultException $ex, RequestInterface $request)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function onDispatchException(\Exception $ex)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function onRender()
    {
    }

    /**
     * {@inheritDoc}
     */
    public function onRenderException(\Exception $ex)
    {
    }
}
