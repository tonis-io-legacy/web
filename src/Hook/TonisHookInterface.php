<?php
namespace Tonis\Mvc\Hook;

use Psr\Http\Message\RequestInterface;
use Tonis\Hookline\HookInterface;
use Tonis\Mvc\Exception\InvalidDispatchResultException;
use Tonis\Router\Match as RouteMatch;

interface TonisHookInterface extends HookInterface
{
    public function onBootstrap();

    /**
     * @param RequestInterface $request
     */
    public function onRoute(RequestInterface $request);

    /**
     * @param RequestInterface $request
     */
    public function onRouteError(RequestInterface $request);

    /**
     * @param RouteMatch $match
     */
    public function onDispatch(RouteMatch $match = null);

    /**
     * @param InvalidDispatchResultException $ex
     * @param RequestInterface $request
     */
    public function onDispatchInvalidResult(InvalidDispatchResultException $ex, RequestInterface $request);

    /**
     * @param \Exception $ex
     */
    public function onDispatchException(\Exception $ex);

    public function onRender();

    /**
     * @param \Exception $ex
     */
    public function onRenderException(\Exception $ex);
}
