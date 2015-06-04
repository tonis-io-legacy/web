<?php
namespace Tonis\Mvc\Factory;

use Tonis\Di\Container;
use Tonis\Di\ContainerUtil;
use Tonis\Di\ServiceFactoryInterface;
use Tonis\Package;
use Tonis\View\ViewManager;

final class ViewManagerFactory implements ServiceFactoryInterface
{
    /**
     * @param Container $di
     * @return ViewManager
     */
    public function createService(Container $di)
    {
        $manager = new ViewManager();
        $config = $di['mvc']['view_manager'];

        foreach ($config['strategies'] as $strategy) {
            if (empty($strategy)) {
                continue;
            }
            $manager->addStrategy(ContainerUtil::get($di, $strategy));
        }

        $manager->setErrorTemplate($config['error_template']);
        $manager->setNotFoundTemplate($config['not_found_template']);

        $di->set(ViewManager::class, $manager);

        return $manager;
    }
}
