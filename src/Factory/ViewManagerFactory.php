<?php
namespace Tonis\Tonis\Factory;

use Tonis\Di\Container;
use Tonis\Package;
use Tonis\Package\PackageManager;
use Tonis\View\ViewManager;

final class ViewManagerFactory
{
    /**
     * @param Container $di
     * @return ViewManager
     */
    public function __invoke(Container $di)
    {
        $vm = new ViewManager();
        $config = $di['config']['tonis']['view_manager'];

        $vm->setErrorTemplate($config['error_template']);
        $vm->setNotFoundTemplate($config['not_found_template']);

        return $vm;
    }
}
