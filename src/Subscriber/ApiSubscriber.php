<?php
namespace Tonis\Web\Subscriber;

use Interop\Container\ContainerInterface;
use Tonis\Event\EventManager;
use Tonis\Event\SubscriberInterface;
use Tonis\Web\LifecycleEvent;
use Tonis\Web\App;
use Tonis\View\Model\JsonModel;
use Tonis\View\Model\StringModel;
use Tonis\View\Strategy\JsonStrategy;
use Tonis\View\ViewManager;

final class ApiSubscriber implements SubscriberInterface
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
        $events->on(App::EVENT_RESPOND, [$this, 'onRespond']);
    }

    public function bootstrapViewManager()
    {
        $vm = $this->di->get(ViewManager::class);
        $vm->addStrategy(new JsonStrategy());
    }

    /**
     * @param LifecycleEvent $event
     */
    public function onDispatch(LifecycleEvent $event)
    {
        $result = $event->getDispatchResult();
        if (is_array($result)) {
            $event->setDispatchResult(new JsonModel($result));
        } elseif (is_string($result)) {
            $event->setDispatchResult(new StringModel($result));
        }
    }

    /**
     * @param LifecycleEvent $event
     */
    public function onDispatchException(LifecycleEvent $event)
    {
        $vars = [
            'error' => 'An error has occurred',
            'message' => $event->getException()->getMessage(),
        ];

        /** @var App $config */
        $app = $this->di->get(App::class);
        if ($app->isDebugEnabled()) {
            $vars['exception'] = get_class($event->getException());
            $vars['trace'] = $event->getException()->getTrace();
        }

        $model = new JsonModel($vars);
        $event->setDispatchResult($model);
    }

    /**
     * @param LifecycleEvent $event
     */
    public function onRouteError(LifecycleEvent $event)
    {
        $event->setResponse($event->getResponse()->withStatus(404));
        $event->setDispatchResult(
            new JsonModel([
                'error' => 'Route could not be matched',
                'path' => $event->getRequest()->getUri()->getPath()
            ])
        );
    }

    /**
     * @param LifecycleEvent $event
     */
    public function onRespond(LifecycleEvent $event)
    {
        $response = $event->getResponse();
        $event->setResponse($response->withHeader('Content-Type', 'application/json'));
    }
}
