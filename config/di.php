<?php
use Tonis\Di\Container;
use Tonis\Di\ContainerUtil;
use Tonis\Mvc\Package\PackageInterface;
use Tonis\Mvc\Tonis;
use Tonis\View\Twig\TwigResolver;
use Tonis\View\Twig\TwigStrategy;
use Tonis\View\Twig\TwigRenderer;

return function(Container $di) {
    $di->set(Twig_Environment::class, function(Container $di) {
        /** @var Tonis $tonis */
        $tonis = $di->get(Tonis::class);
        $pm = $tonis->getPackageManager();

        $paths = [];
        foreach ($pm->getPackages() as $package) {
            if ($package instanceof PackageInterface) {
                $path = realpath($package->getPath() . '/view');
                if ($path) {
                    $paths[] = $path;
                }
            }
        }

        $config = $di['tonis']['twig'];
        $loader = new \Twig_Loader_Filesystem(array_reverse($paths));
        $twig = new \Twig_Environment($loader, $config['options']);

        foreach ($config['extensions'] as $extension) {
            $twig->addExtension(ContainerUtil::get($di, $extension));
        }

        return $twig;
    });

    $di->set(TwigStrategy::class, function(Container $di) {
        return new TwigStrategy($di->get(\Twig_Environment::class));
    });

    $di->set(\League\Plates\Engine::class, function(Container $di) {
        /** @var Tonis $tonis */
        $tonis = $di->get(Tonis::class);
        $pm = $tonis->getPackageManager();

        $engine = new \League\Plates\Engine(__DIR__ . '/../view');

        foreach ($pm->getPackages() as $package) {
            if ($package instanceof PackageInterface) {
                $path = realpath($package->getPath() . '/view');
                if ($path) {
                    $engine->addFolder('main', $path);
                }
            }
        }

        return $engine;
    });

    $di->set(\Tonis\View\Plates\PlatesStrategy::class, function(Container $di) {
        return new \Tonis\View\Plates\PlatesStrategy($di->get(\League\Plates\Engine::class));
    });
};
