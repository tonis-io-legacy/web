<?php
namespace Tonis\Mvc\Hook;

use Psr\Http\Message\RequestInterface;
use Tonis\Hookline\HookInterface;
use Tonis\Mvc\App;

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
}
