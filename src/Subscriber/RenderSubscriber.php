<?php
namespace Tonis\Mvc\Subscriber;

use Tonis\Di\ContainerUtil;
use Tonis\Event\EventManager;
use Tonis\Event\SubscriberInterface;
use Tonis\Mvc\Exception\InvalidTemplateException;
use Tonis\Mvc\LifecycleEvent;
use Tonis\Mvc\Tonis;
use Tonis\View\Model\ViewModel;
use Tonis\View\ModelInterface;
use Tonis\View\ViewManager;

final class RenderSubscriber implements SubscriberInterface
{
    /**
     * {@inheritDoc}
     */
    public function subscribe(EventManager $events)
    {
        $events->on(Tonis::EVENT_RENDER, [$this, 'prepareViewManager']);
        $events->on(Tonis::EVENT_RENDER, [$this, 'onRender']);
    }

    /**
     * @param LifecycleEvent $event
     */
    public function prepareViewManager(LifecycleEvent $event)
    {
        $tonis = $event->getTonis();
        $di = $tonis->di();
        $vm = $tonis->getViewManager();

        $config = $di['mvc']['view_manager'];

        foreach ($config['strategies'] as $strategy) {
            if (empty($strategy)) {
                continue;
            }
            $vm->addStrategy(ContainerUtil::get($di, $strategy));
        }

        $vm->setErrorTemplate($config['error_template']);
        $vm->setNotFoundTemplate($config['not_found_template']);
    }

    /**
     * @param LifecycleEvent $lifecycle
     */
    public function onRender(LifecycleEvent $lifecycle)
    {
        $viewManager = $lifecycle->getTonis()->getViewManager();
        $dispatchResult = $lifecycle->getDispatchResult();

        if ($dispatchResult instanceof ViewModel && !$dispatchResult->getTemplate()) {
            $match = $lifecycle->getRouteMatch();
            $handler = $match->getRoute()->getHandler();
            $dispatchResult = $this->createTemplateModel($viewManager, $dispatchResult, $handler);
        }

        if (!$dispatchResult instanceof ModelInterface) {
            $dispatchResult = new ViewModel($viewManager->getErrorTemplate());
        }

        $lifecycle->setRenderResult($viewManager->render($dispatchResult));
    }

    /**
     * @param ViewManager $viewManager
     * @param ViewModel $model
     * @param mixed $handler
     * @return ViewModel
     */
    private function createTemplateModel(ViewManager $viewManager, ViewModel $model, $handler)
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
            $viewManager->getErrorTemplate(),
            [
                'type' => 'no-template-available',
                'exception' => new InvalidTemplateException()
            ]
        );
    }
}
