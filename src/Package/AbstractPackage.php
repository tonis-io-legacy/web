<?php
namespace Tonis\Mvc\Package;

use Interop\Container\ContainerInterface;
use Tonis\Router\RouteCollection;

abstract class AbstractPackage implements PackageInterface
{
    /** @var string */
    private $path;
    /** @var string */
    private $name;
    /** @var string */
    private $namespace;

    /**
     * {@inheritDoc}
     */
    final public function getName()
    {
        if ($this->name) {
            return $this->name;
        }
        $replace = function ($match) {
            return $match[1] . '-' . $match[2];
        };
        $name = preg_replace('@Package$@', '', $this->getNamespace());
        $name = str_replace('\\', '.', $name);
        $name = preg_replace_callback('@([a-z])([A-Z])@', $replace, $name);
        $name = strtolower($name);
        if (strstr($name, '.')) {
            $this->name = substr($name, strpos($name, '.') + 1);
        } else {
            $this->name = $name;
        }
        return $this->name;
    }
    /**
     * {@inheritDoc}
     */
    final public function getNamespace()
    {
        if ($this->namespace) {
            return $this->namespace;
        }
        $class = get_class($this);
        $this->namespace = substr($class, 0, strrpos($class, '\\'));
        return $this->namespace;
    }

    /**
     * {@inheritDoc}
     */
    public function configureRoutes(RouteCollection $routes)
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
    public function configureDi(ContainerInterface $di)
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
        if (!file_exists($path . '/config/package.config.php')) {
            return [];
        }
        return include $path . '/config/package.config.php';
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
        $this->path = realpath(preg_replace('@/src/?$@', '', dirname($refl->getFileName())));
        return $this->path;
    }
}
