<?php
namespace Tonis\Web\Subscriber;

use Interop\Container\ContainerInterface;
use Tonis\Event\EventManager;
use Tonis\Event\SubscriberInterface;
use Tonis\Web\LifecycleEvent;
use Tonis\Web\Tonis;
use Tonis\Router\RouteMatch;
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
        $events->on(Tonis::EVENT_BOOTSTRAP, [$this, 'bootstrapViewManager']);
        $events->on(Tonis::EVENT_ROUTE_ERROR, [$this, 'onRouteError']);
        $events->on(Tonis::EVENT_DISPATCH, [$this, 'onDispatch']);
        $events->on(Tonis::EVENT_DISPATCH_EXCEPTION, [$this, 'onDispatchException']);
        $events->on(Tonis::EVENT_RESPOND, [$this, 'onRespond']);
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
        $model = new JsonModel([
            'error' => 'An error has occurred',
            'exception' => get_class($event->getException()),
            'message' => $event->getException()->getMessage(),
            'trace' => $event->getException()->getTrace()
        ]);
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
