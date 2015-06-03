<?php
namespace Tonis\Mvc\Factory;

use Tonis\Di;
use Tonis\View;
use Tonis\Package;

final class ViewManagerFactory implements Di\ServiceFactoryInterface
{
    /**
     * @param Di\Container $di
     * @return View\Manager
     */
    public function createService(Di\Container $di)
    {
        $pm = $di->get(Package\Manager::class);

        $manager = new View\Manager();
        $config = $pm->getMergedConfig()['tonis']['view_manager'];

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
