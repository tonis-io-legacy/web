<?php
namespace Tonis\Web\Subscriber;

use Interop\Container\ContainerInterface;
use Tonis\Event\EventManager;
use Tonis\Event\SubscriberInterface;
use Tonis\Web\Exception\InvalidDispatchResultException;
use Tonis\Web\Exception\InvalidTemplateException;
use Tonis\Web\LifecycleEvent;
use Tonis\Web\App;
use Tonis\Router\RouteMatch;
use Tonis\View\Model\StringModel;
use Tonis\View\Model\ViewModel;
use Tonis\View\Strategy\PlatesStrategy;
use Tonis\View\Strategy\StringStrategy;
use Tonis\View\ViewManager;

final class WebSubscriber implements SubscriberInterface
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
        $events->on(App::EVENT_BOOTSTRAP, [$this, 'bootstrapViewManager']);
        $events->on(App::EVENT_ROUTE_ERROR, [$this, 'onRouteError']);
        $events->on(App::EVENT_DISPATCH, [$this, 'onDispatch']);
        $events->on(App::EVENT_DISPATCH_EXCEPTION, [$this, 'onDispatchException']);
        $events->on(App::EVENT_RENDER_EXCEPTION, [$this, 'onRenderException']);

    }

    public function bootstrapViewManager()
    {
        $vm = $this->di->get(ViewManager::class);
        $vm->addStrategy(new StringStrategy());
        $vm->addStrategy($this->di->get(PlatesStrategy::class));
    }

    /**
     * @param LifecycleEvent $event
     */
    public function onRouteError(LifecycleEvent $event)
    {
        $match = $event->getRouteMatch();
        if (!$match instanceof RouteMatch) {
            $vm = $this->di->get(ViewManager::class);

            $event->setDispatchResult(
                new ViewModel(
                    $vm->getNotFoundTemplate(),
                    [
                        'path' => $event->getRequest()->getUri()->getPath(),
                        'type' => 'route'
                    ]
                )
            );
        }
    }

    /**
     * @param LifecycleEvent $event
     */
    public function onDispatch(LifecycleEvent $event)
    {
        $dispatchResult = $event->getDispatchResult();
        if (is_array($dispatchResult)) {
            $dispatchResult = new ViewModel(
                isset($dispatchResult['$$template']) ? $dispatchResult['$$template'] : null,
                $dispatchResult
            );
        } elseif (is_string($dispatchResult)) {
            $dispatchResult = new StringModel($dispatchResult);
        }

        if ($dispatchResult instanceof ViewModel && !$dispatchResult->getTemplate()) {
            $match = $event->getRouteMatch();
            $handler = $match->getRoute()->getHandler();
            $dispatchResult = $this->createTemplateModel($dispatchResult, $handler);
        }

        $event->setDispatchResult($dispatchResult);
    }

    /**
     * @param LifecycleEvent $event
     */
    public function onDispatchException(LifecycleEvent $event)
    {
        $event->setDispatchResult($this->createExceptionModel($event));
    }

    /**
     * @param LifecycleEvent $event
     */
    public function onRenderException(LifecycleEvent $event)
    {
        $vm = $this->di->get(ViewManager::class);
        $model = $this->createExceptionModel($event);

        $event->setRenderResult($vm->render($model));
    }

    /**
     * @param LifecycleEvent $event
     * @return ViewModel
     */
    private function createExceptionModel(LifecycleEvent $event)
    {
        $vm = $this->di->get(ViewManager::class);
        $type = 'exception';

        switch (get_class($event->getException())) {
            case InvalidDispatchResultException::class:
                $type = 'invalid-dispatch-result';
                break;
        }

        return new ViewModel(
            $vm->getErrorTemplate(),
            [
                'exception' => $event->getException(),
                'type' => $type,
                'path' => $event->getRequest()->getUri()->getPath()
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
        $action = null;
        if (is_array($handler)) {
            $action = $handler[1];
            $handler = $handler[0];
        }
        if (is_object($handler) && !$handler instanceof \Closure) {
            $handler = get_class($handler);
        }
        if (is_string($handler)) {
            $replace = function ($match) {
                return $match[1] . '-' . $match[2];
            };

            $template = preg_replace_callback('@([a-z])([A-Z])@', $replace, $handler);
            $template = strtolower($template);
            $template = '@' . str_replace('\\', '/', $template);

            // strip the final suffix (-action -controller, etc).
            $template = preg_replace('@-\w+$@', '', $template);

            // replace the final part with the action if necessary
            if ($action) {
                $template = preg_replace('@/\w+$@', '/' . $action, $template);
            }
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
