<?php
namespace Tonis\Web\Subscriber;

use Interop\Container\ContainerInterface;
use Tonis\Di\ContainerUtil;
use Tonis\Di\ServiceFactoryInterface;
use Tonis\Dispatcher\Dispatcher;
use Tonis\Event\EventManager;
use Tonis\Event\SubscriberInterface;
use Tonis\Router\Router;
use Tonis\Web\Exception\InvalidDispatchResultException;
use Tonis\Web\LifecycleEvent;
use Tonis\Web\App;
use Tonis\Router\RouteMatch;
use Tonis\View\ModelInterface;
use Tonis\View\ViewManager;
use Zend\Diactoros\Response;

final class BaseSubscriber implements SubscriberInterface
{
    /** @var ContainerInterface */
    private $serviceContainer;

    /**
     * @param ContainerInterface $serviceContainer
     */
    public function __construct(ContainerInterface $serviceContainer)
    {
        $this->serviceContainer = $serviceContainer;
    }

    /**
     * @param EventManager $events
     * @return void
     */
    public function subscribe(EventManager $events)
    {
        $events->on(App::EVENT_ROUTE, [$this, 'onRoute']);
        $events->on(App::EVENT_ROUTE_ERROR, [$this, 'onRouteError']);
        $events->on(App::EVENT_DISPATCH, [$this, 'onDispatch'], 1000);

        // This needs to run as the last Dispatch event which detects if the dispatch result is valid
        $events->on(App::EVENT_DISPATCH, [$this, 'onDispatchValidateResult'], -1000);

        $events->on(App::EVENT_RENDER, [$this, 'onRender']);
        $events->on(App::EVENT_RESPOND, [$this, 'onRespond']);

        $events->on(App::EVENT_DISPATCH_EXCEPTION, [$this, 'onDispatchException']);
    }

    public function bootstrapPackageSubscribers()
    {
        /** @var App $app */
        $app = $this->serviceContainer->get(App::class);
        $subscribers = $this->serviceContainer['config']['tonis']['subscribers'];

        foreach ($subscribers as $subscriber) {
            $app->getEventManager()->subscribe(ContainerUtil::get($this->serviceContainer, $subscriber));
        }
    }

    /**
     * @param LifecycleEvent $event
     */
    public function onRoute(LifecycleEvent $event)
    {
        $match = $this->serviceContainer->get(Router::class)->match($event->getRequest());
        if ($match instanceof RouteMatch) {
            $event->setRouteMatch($match);
        }
    }

    /**
     * @param LifecycleEvent $event
     */
    public function onRouteError(LifecycleEvent $event)
    {
        $event->setResponse($event->getResponse()->withStatus(404));
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

        $params = $routeMatch->getParams();

        if (!isset($params['response'])) {
            $params['response'] = $event->getResponse();
        }
        if (!isset($params['request'])) {
            $params['request'] = $event->getRequest();
        }

        /** @var Dispatcher $dispatcher */
        $dispatcher = $this->serviceContainer->get(Dispatcher::class);
        $handler = $routeMatch->getRoute()->getHandler();
        $result = $dispatcher->dispatch($this->introspectHandler($handler), $params);

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
    public function onDispatchException(LifecycleEvent $event)
    {
        $event->setResponse($event->getResponse()->withStatus(500));
    }

    /**
     * @param LifecycleEvent $event
     */
    public function onRender(LifecycleEvent $event)
    {
        if (null !== $event->getRenderResult()) {
            return;
        }

        $vm = $this->serviceContainer->get(ViewManager::class);
        $event->setRenderResult($vm->render($event->getDispatchResult()));
    }

    /**
     * @param LifecycleEvent $event
     */
    public function onRespond(LifecycleEvent $event)
    {
        $response = $event->getResponse() ? $event->getResponse() : new Response;
        $response->getBody()->write($event->getRenderResult());
    }

    /**
     * @param string $handler
     * @return array|mixed
     */
    private function introspectHandler($handler)
    {
        if (is_string($handler) && $this->serviceContainer->has($handler)) {
            return $this->serviceContainer->get($handler);
        }
        if (is_array($handler) && is_string($handler[0]) && $this->serviceContainer->has($handler[0])) {
            $handler[0] = $this->serviceContainer->get($handler[0]);
            return $handler;
        }

        return $handler;
    }
}
