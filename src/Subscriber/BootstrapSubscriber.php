<?php
namespace Tonis\Mvc\Subscriber;

use Interop\Container\ContainerInterface;
use Tonis\Di\ContainerUtil;
use Tonis\Di\ServiceFactoryInterface;
use Tonis\Dispatcher\Dispatcher;
use Tonis\Event\EventManager;
use Tonis\Event\SubscriberInterface;
use Tonis\Mvc\Exception\InvalidDispatchResultException;
use Tonis\Mvc\LifecycleEvent;
use Tonis\Mvc\Package\PackageInterface;
use Tonis\Mvc\Tonis;
use Tonis\Package\PackageManager;
use Tonis\Router\RouteCollection;
use Tonis\Router\RouteMatch;
use Tonis\View\ModelInterface;
use Tonis\View\ViewManager;

final class BootstrapSubscriber implements SubscriberInterface
{
    /** @var ContainerInterface */
    private $di;

    /**
     * @param ContainerInterface $di
     */
    public function __construct(ContainerInterface $di)
    {
        $this->di = $di;
    }

    /**
     * @param EventManager $events
     * @return void
     */
    public function subscribe(EventManager $events)
    {
        $events->on(Tonis::EVENT_BOOTSTRAP, [$this, 'bootstrapPackageManager']);
        $events->on(Tonis::EVENT_BOOTSTRAP, [$this, 'bootstrapSubscribers']);

        $events->on(Tonis::EVENT_ROUTE, [$this, 'onRoute']);
        $events->on(Tonis::EVENT_DISPATCH, [$this, 'onDispatch'], 1000);

        // This needs to run as the last Dispatch event which detects if the dispatch result is valid
        $events->on(Tonis::EVENT_DISPATCH, [$this, 'onDispatchValidateResult'], -1000);

        $events->on(Tonis::EVENT_RENDER, [$this, 'onRender']);
    }

    public function bootstrapSubscribers()
    {
        /** @var Tonis $tonis */
        $tonis = $this->di->get(Tonis::class);
        $subscribers = $this->di->get(PackageManager::class)->getMergedConfig()['mvc']['subscribers'];

        foreach ($subscribers as $subscriber) {
            $tonis->events()->subscribe(ContainerUtil::get($this->di, $subscriber));
        }
    }

    public function bootstrapPackageManager()
    {
        /** @var Tonis $tonis */
        $tonis = $this->di->get(Tonis::class);

        $pm = $tonis->getPackageManager();
        $pm->load();

        foreach ($pm->getPackages() as $package) {
            if ($package instanceof PackageInterface) {
                $package->configureServices($tonis->di());
                $package->bootstrap($tonis);
                $package->configureRoutes($tonis->routes());
            }
        }
    }

    /**
     * @param LifecycleEvent $event
     */
    public function onRoute(LifecycleEvent $event)
    {
        $match = $this->di->get(RouteCollection::class)->match($event->getRequest());
        if ($match instanceof RouteMatch) {
            $event->setRouteMatch($match);
        }
    }

    /**
     * @param LifecycleEvent $event
     */
    public function onDispatch(LifecycleEvent $event)
    {
        if (null !== $event->getDispatchResult()) {
            return;
        }

        $routeMatch = $event->getRouteMatch();
        if (!$routeMatch instanceof RouteMatch) {
            return;
        }

        $dispatcher = $this->di->get(Dispatcher::class);
        $handler = $routeMatch->getRoute()->getHandler();
        $result = $dispatcher->dispatch($handler, $routeMatch->getParams());

        if ($result instanceof ServiceFactoryInterface) {
            $result = $dispatcher->dispatch($result->createService($this->di), $routeMatch->getParams());
        }

        $event->setDispatchResult($result);
    }

    /**
     * @param LifecycleEvent $event
     */
    public function onDispatchValidateResult(LifecycleEvent $event)
    {
        $result = $event->getDispatchResult();
        if (!$result instanceof ModelInterface) {
            throw new InvalidDispatchResultException();
        }
    }

    /**
     * @param LifecycleEvent $event
     */
    public function onRender(LifecycleEvent $event)
    {
        if (null !== $event->getRenderResult()) {
            return;
        }

        $vm = $this->di->get(ViewManager::class);
        $event->setRenderResult($vm->render($event->getDispatchResult()));
    }
}
