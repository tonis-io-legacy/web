<?php
namespace Tonis\Web\Factory;

use Tonis\Di\Container;
use Tonis\Di\ContainerUtil;
use Tonis\Package;
use Tonis\View\ViewManager;

final class ViewManagerFactory
{
    /**
     * @param Container $di
     * @return ViewManager
     */
    public function __invoke(Container $di)
    {
        $config = $di['config']['tonis']['view_manager'];
        $vm = new ViewManager(ContainerUtil::get($di, $config['fallback_strategy']));

        $vm->setErrorTemplate($config['error_template']);
        $vm->setNotFoundTemplate($config['not_found_template']);

        return $vm;
    }
}
