<?php
namespace Tonis\Web\Package;

use Interop\Container\ContainerInterface;
use Tonis\Router\Router;
use Tonis\Web\App;
use Tonis\Web\Console;
use Tonis\Web\Factory\DispatchableFactory;

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
    public function bootstrap(App $app)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function bootstrapConsole(Console $console)
    {
    }

    /**
     * @param Router $router
     */
    public function configureRoutes(Router $router)
    {
    }

    /**
     * @param ContainerInterface $di
     */
    public function configureServices(ContainerInterface $di)
    {
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

        $parts = explode('\\', $this->getNamespace());
        $name = preg_replace_callback('@([a-z])([A-Z])@', $replace, $parts[count($parts) -1]);
        $this->name = strtolower($name);

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
}
