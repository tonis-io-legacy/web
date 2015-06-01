<?php
namespace Tonis\Mvc\Hook;

use Psr\Http\Message\RequestInterface;
use Tonis\Hookline\HookInterface;
use Tonis\Mvc\Exception\InvalidDispatchResultException;
use Tonis\Mvc\Tonis;
use Tonis\Router\Match as RouteMatch;
use Tonis\View\Manager as ViewManager;

interface TonisHookInterface extends HookInterface
{
    /**
     * @param Tonis $tonis
     * @param array $config
     */
    public function onBootstrap(Tonis $tonis, array $config);

    /**
     * @param Tonis $tonis
     * @param RequestInterface $request
     */
    public function onRoute(Tonis $tonis, RequestInterface $request);

    /**
     * @param Tonis $tonis
     * @param RequestInterface $request
     */
    public function onRouteError(Tonis $tonis, RequestInterface $request);

    /**
     * @param Tonis $tonis
     * @param RouteMatch $match
     */
    public function onDispatch(Tonis $tonis, RouteMatch $match = null);

    /**
     * @param Tonis $tonis
     * @param InvalidDispatchResultException $ex
     * @param RequestInterface $request
     */
    public function onDispatchInvalidResult(Tonis $tonis, InvalidDispatchResultException $ex, RequestInterface $request);

    /**
     * @param Tonis $tonis
     * @param \Exception $ex
     */
    public function onDispatchException(Tonis $tonis, \Exception $ex);

    /**
     * @param Tonis $tonis
     * @param ViewManager $vm
     */
    public function onRender(Tonis $tonis, ViewManager $vm);

    /**
     * @param Tonis $tonis
     * @param \Exception $ex
     */
    public function onRenderException(Tonis $tonis, \Exception $ex);
}
