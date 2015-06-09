<?php
namespace Tonis\Mvc\Subscriber;

use Interop\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Tonis\Event\EventManager;
use Tonis\Event\SubscriberInterface;
use Tonis\Mvc\Exception\InvalidDispatchResultException;
use Tonis\Mvc\Exception\InvalidTemplateException;
use Tonis\Mvc\LifecycleEvent;
use Tonis\Mvc\Tonis;
use Tonis\Router\RouteMatch;
use Tonis\View\Model\ViewModel;
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
        $events->on(Tonis::EVENT_ROUTE_ERROR, [$this, 'onRouteError']);
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
     * @param ViewManager $vm
     * @param RequestInterface $request
     * @param \Exception $exception
     * @return ViewModel
     */
    private function createExceptionModel(ViewManager $vm, RequestInterface $request, \Exception $exception)
    {
        $type = 'unknown';

        switch (get_class($exception)) {
            case InvalidDispatchResultException::class:
                $type = 'invalid-dispatch-result';
                break;
        }

        return new ViewModel(
            $vm->getErrorTemplate(),
            [
                'exception' => $exception,
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
            $template = '@' . str_replace('\\', '/', $template);

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
