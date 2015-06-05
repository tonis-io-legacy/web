<?php
namespace Tonis\Mvc\Subscriber;

use Tonis\Dispatcher\Dispatcher;
use Tonis\Event\EventManager;
use Tonis\Event\SubscriberInterface;
use Tonis\Mvc\Exception\InvalidDispatchResultException;
use Tonis\Mvc\LifecycleEvent;
use Tonis\Mvc\Tonis;
use Tonis\View\Model\StringModel;
use Tonis\View\Model\ViewModel;
use Tonis\View\ModelInterface;

final class DispatchSubscriber implements SubscriberInterface
{
    /** @var Dispatcher */
    private $dispatcher;

    /**
     * @param Dispatcher $dispatcher
     */
    public function __construct(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * {@inheritDoc}
     */
    public function subscribe(EventManager $events)
    {
        $events->on(Tonis::EVENT_DISPATCH, [$this, 'onDispatch']);
    }

    /**
     * @param LifecycleEvent $lifecycle
     */
    public function onDispatch(LifecycleEvent $lifecycle)
    {
        if (null !== $lifecycle->getDispatchResult()) {
            return;
        }

        $routeMatch = $lifecycle->getRouteMatch();
        $handler = $routeMatch->getRoute()->getHandler();
        $result = $this->dispatcher->dispatch($handler, $routeMatch->getParams());

        if (is_array($result)) {
            $result = new ViewModel(null, $result);
        } elseif (is_string($result)) {
            $result = new StringModel($result);
        }

        if (!$result instanceof ModelInterface) {
            $lifecycle->setException(new InvalidDispatchResultException());
        }

        $lifecycle->setDispatchResult($result);
    }
}
