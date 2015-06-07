<?php
namespace Tonis\Mvc\Subscriber;

use Interop\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Tonis\Dispatcher\Dispatcher;
use Tonis\Event\EventManager;
use Tonis\Event\SubscriberInterface;
use Tonis\Mvc\Exception\InvalidDispatchResultException;
use Tonis\Mvc\Exception\InvalidTemplateException;
use Tonis\Mvc\LifecycleEvent;
use Tonis\Mvc\Tonis;
use Tonis\Router\RouteCollection;
use Tonis\Router\RouteMatch;
use Tonis\View\Model\StringModel;
use Tonis\View\Model\ViewModel;
use Tonis\View\ModelInterface;
use Tonis\View\ViewManager;

final class HttpSubscriber implements SubscriberInterface
{
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
        $events->on(Tonis::EVENT_ROUTE, [$this, 'onRoute']);
        $events->on(Tonis::EVENT_DISPATCH, [$this, 'onDispatch']);
        $events->on(Tonis::EVENT_RENDER, [$this, 'onRender']);
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

        $handler = $routeMatch->getRoute()->getHandler();
        $result = $this->di->get(Dispatcher::class)->dispatch($handler, $routeMatch->getParams());

        if (is_array($result)) {
            $result = new ViewModel(null, $result);
        } elseif (is_string($result)) {
            $result = new StringModel($result);
        }

        if (!$result instanceof ModelInterface) {
            $event->setException(new InvalidDispatchResultException());
        }

        $event->setDispatchResult($result);
    }

    /**
     * @param LifecycleEvent $event
     */
    public function onRender(LifecycleEvent $event)
    {
        if (null !== $event->getRenderResult()) {
            return;
        }

        $dispatchResult = $event->getDispatchResult();
        if ($dispatchResult instanceof ViewModel && !$dispatchResult->getTemplate()) {
            $match = $event->getRouteMatch();
            $handler = $match->getRoute()->getHandler();
            $dispatchResult = $this->createTemplateModel($dispatchResult, $handler);
        }

        $vm = $this->di->get(ViewManager::class);

        if (!$dispatchResult instanceof ModelInterface) {
            $dispatchResult = $this->createExceptionModel(
                $vm,
                $event->getRequest(),
                new InvalidDispatchResultException(),
                'invalid-dispatch-result'
            );
        }

        $event->setRenderResult($vm->render($dispatchResult));
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
     * @param ViewManager $vm
     * @param RequestInterface $request
     * @param \Exception $ex
     * @param string $type
     * @return ViewModel
     */
    private function createExceptionModel(ViewManager $vm, RequestInterface $request, \Exception $ex, $type)
    {
        return new ViewModel(
            $vm->getErrorTemplate(),
            [
                'exception' => $ex,
                'type' => $type,
                'path' => $request->getUri()->getPath()
            ]
        );
    }

    /**
     * @param ViewModel $model
     * @param mixed $handler
     * @return ViewModel
     */
    private function createTemplateModel(ViewModel $model, $handler)
    {
        if (is_array($handler)) {
            $handler = $handler[0];
        }
        if (is_object($handler)) {
            $handler = get_class($handler);
        }
        if (is_string($handler)) {
            $replace = function ($match) {
                return $match[1] . '-' . $match[2];
            };

            $template = preg_replace('@Action$@', '', $handler);
            $template = preg_replace_callback('@([a-z])([A-Z])@', $replace, $template);
            $template = strtolower($template);
            $template = str_replace('\\', '/', $template);

            return new ViewModel($template, $model->getVariables());
        }

        return new ViewModel(
            $this->di->get(ViewManager::class)->getErrorTemplate(),
            [
                'type' => 'no-template-available',
                'exception' => new InvalidTemplateException()
            ]
        );
    }
}
