<?php
namespace Tonis\Web\Factory;

use Tonis\Di\Container;
use Tonis\Di\ContainerUtil;
use Tonis\Package;
use Tonis\View\ViewManager;

final class ViewManagerFactory
{
    /**
     * @param Container $services
     * @return ViewManager
     */
    public function __invoke(Container $services)
    {
        $config = $services['config']['tonis']['view_manager'];
        $vm = new ViewManager(ContainerUtil::get($services, $config['fallback_strategy']));

        $vm->setErrorTemplate($config['error_template']);
        $vm->setNotFoundTemplate($config['not_found_template']);

        return $vm;
    }
}
