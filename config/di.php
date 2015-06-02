<?php
use Tonis\Di;
use Tonis\Hookline;
use Tonis\Mvc;
use Tonis\View;

return function(Di\Container $di) {















    $di->set(Twig_Environment::class, function(Container $di) {
        /** @var Tonis $tonis */
        $tonis = $di->get(Tonis::class);
        $pm = $tonis->getPackageManager();

        $paths = [];
        foreach ($pm->getPackages() as $package) {
            if ($package instanceof PackageInterface) {
                $path = realpath($package->getPath() . '/view');
                if ($path) {
                    $paths[$package->getName()] = $path;
                }
            }
        }

        $loader = new \Twig_Loader_Filesystem();

        foreach ($paths as $namespace => $path) {
            $loader->addPath($path, $namespace);
        }


        foreach ($di['tonis']['twig']['namespaces'] as $namespace => $path) {
            $loader->addPath($path, $namespace);
        }

        $twig = new \Twig_Environment($loader, $di['tonis']['twig']['options']);

        foreach ($di['tonis']['twig']['extensions'] as $extension) {
            $twig->addExtension(ContainerUtil::get($di, $extension));
        }

        return $twig;
    });

    $di->set(TwigStrategy::class, function(Container $di) {
        return new TwigStrategy($di->get(\Twig_Environment::class));
    });

    $di->set(PlatesEngine::class, function(Container $di) {
        /** @var Tonis $tonis */
        $tonis = $di->get(Tonis::class);
        $pm = $tonis->getPackageManager();

        $engine = new PlatesEngine();

        foreach ($pm->getPackages() as $package) {
            if ($package instanceof PackageInterface) {
                $path = realpath($package->getPath() . '/view');
                if ($path) {
                    $engine->addFolder($package->getName(), $path);
                }
            }
        }

        foreach ($di['tonis']['plates']['folders'] as $name => $path) {
            $engine->addFolder($name, $path);
        }

        return $engine;
    });

    $di->set(PlatesStrategy::class, function(Container $di) {
        return new PlatesStrategy($di->get(PlatesEngine::class));
    });
};
