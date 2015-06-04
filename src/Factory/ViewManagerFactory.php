<?php
namespace Tonis\Mvc\Factory;

use Tonis\Di;
use Tonis\View;
use Tonis\Package;
use Tonis\View\ViewManager;

final class ViewManagerFactory implements Di\ServiceFactoryInterface
{
    /**
     * @param Di\Container $di
     * @return ViewManager
     */
    public function createService(Di\Container $di)
    {
        $manager = new ViewManager();
        $config = $di['tonis']['view_manager'];

        foreach ($config['strategies'] as $strategy) {
            if (empty($strategy)) {
                continue;
            }

            $manager->addStrategy(Di\ContainerUtil::get($di, $strategy));
        }

        $manager->setErrorTemplate($config['error_template']);
        $manager->setNotFoundTemplate($config['not_found_template']);

        return $manager;
    }
}
