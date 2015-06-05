<?php
namespace Tonis\Mvc;

use Tonis\Event\EventManager;
use Tonis\Event\SubscriberInterface;
use Tonis\View\Model\ViewModel;
use Tonis\View\ViewManager;

final class RenderSubscriber implements SubscriberInterface
{
    /** @var ViewManager */
    private $viewManager;

    /**
     * @param ViewManager $viewManager
     */
    public function __construct(ViewManager $viewManager)
    {
        $this->viewManager = $viewManager;
    }

    /**
     * {@inheritDoc}
     */
    public function subscribe(EventManager $events)
    {
        $events->on(Tonis::EVENT_RENDER, [$this, 'onRender']);
    }

    public function onRender(LifecycleEvent $lifecycle)
    {
        $dispatchResult = $lifecycle->getDispatchResult();

        if ($dispatchResult instanceof ViewModel && !$dispatchResult->getTemplate()) {
            $match = $this->getRouteCollection()->getLastMatch();
            $handler = $match->getRoute()->getHandler();

            if (is_array($handler)) {
                $handler = $handler[0];
            }

            if (is_object($handler)) {
                $handler = get_class($handler);
            }

            if (is_string($handler)) {
                $replace = function($match) {
                    return $match[1] . '-' . $match[2];
                };
                $template = preg_replace('@Action$@', '', $handler);
                $template = preg_replace_callback('@([a-z])([A-Z])@', $replace, $template);
                $template = strtolower($template);
                $template = str_replace('\\', '/', $template);

                $dispatchResult = new View\Model\ViewModel($template, $dispatchResult->getVariables());
            } else {
                return $this->getExceptionViewModel(
                    'no-template-available',
                    new Exception\InvalidTemplateException('No template was available for rendering')
                );
            }
        }

        return $this->viewManager->render($dispatchResult);
    }
}
