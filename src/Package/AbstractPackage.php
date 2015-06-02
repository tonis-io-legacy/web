<?php
namespace Tonis\Mvc\Package;

use Tonis\Di\Container;
use Tonis\Mvc\Tonis;
use Tonis\Mvc\TonisConsole;
use Tonis\Router\Collection;

abstract class AbstractPackage implements PackageInterface
{
    /** @var string */
    private $path;

    /**
     * {@inheritDoc}
     */
    public function bootstrap(Tonis $tonis)
    {}

    /**
     * {@inheritDoc}
     */
    public function bootstrapConsole(TonisConsole $console)
    {}

    /**
     * {@inheritDoc}
     */
    public function configureRoutes(Collection $routes)
    {
        $path = $this->getPath();
        if (file_exists($path . '/config/routes.php')) {
            $callable = include $path . '/config/routes.php';

            if (!is_callable($callable)) {
                throw new \RuntimeException('Default MVC package expects routes config to return a callable');
            }

            $callable($routes);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function configureDi(Container $di)
    {
        $path = $this->getPath();
        if (file_exists($path . '/config/di.php')) {
            $callable = include $path . '/config/di.php';

            if (!is_callable($callable)) {
                throw new \RuntimeException('Default MVC package expects di config to return a callable');
            }

            $callable($di);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getConfig()
    {
        $path = $this->getPath();
        if (!file_exists($path . '/config/package.php')) {
            return [];
        }
        return include $path . '/config/package.php';
    }

    /**
     * {@inheritDoc}
     */
    public function getPath()
    {
        if ($this->path) {
            return $this->path;
        }

        $refl = new \ReflectionObject($this);
        $this->path = realpath(dirname($refl->getFileName()) . '/../');
        return $this->path;
    }
}
