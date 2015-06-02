<?php
namespace Tonis\Mvc\Hook;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\RequestInterface;
use Tonis\Di\ServiceFactoryInterface;
use Tonis\Dispatcher\Dispatcher;
use Tonis\Mvc\Exception\InvalidDispatchResultException;
use Tonis\Mvc\Exception\InvalidTemplateException;
use Tonis\Mvc\Exception\InvalidViewModelException;
use Tonis\Mvc\Tonis;
use Tonis\Router\Match as RouteMatch;
use Tonis\View\Model\StringModel;
use Tonis\View\Model\ViewModel;
use Tonis\View\ModelInterface as ViewModelInterface;
use Tonis\View\ModelInterface;

final class DefaultTonisHook extends AbstractTonisHook
{
    /** @var Tonis */
    private $tonis;

    /**
     * @param Tonis $tonis
     */
    public function __construct(Tonis $tonis)
    {
        $this->tonis = $tonis;
    }

    /**
     * {@inheritDoc}
     */
    public function onDispatch(RouteMatch $match = null)
    {
        if (null !== $this->tonis->getDispatchResult()) {
            return;
        }
        if (!$match instanceof RouteMatch) {
            return;
        }

        $handler = $match->getRoute()->getHandler();
        $di = $this->tonis->getDi();
        $dispatcher = new Dispatcher();

        if (is_string($handler) && $di->has($handler)) {
            $handler = $di->get($handler);
        }

        try {
            $result = $dispatcher->dispatch($handler, $match->getParams());

            if ($result instanceof ServiceFactoryInterface) {
                $result = $dispatcher->dispatch($result->createService($di), $match->getParams());
            }

            if ($result === $handler) {
                $result = new InvalidDispatchResultException();
            } elseif (is_array($result)) {
                $result = new ViewModel($result);
            } elseif (is_string($result)) {
                $result = new StringModel($result);
            } elseif (!$result instanceof ViewModelInterface) {
                $result = new InvalidDispatchResultException('Failed to dispatch; invalid dispatch result');
            }

            $this->tonis->setDispatchResult($result);
        } catch (\Exception $ex) {
            $this->tonis->setDispatchResult($ex);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function onDispatchInvalidResult(InvalidDispatchResultException $ex, RequestInterface $request)
    {
        $model = new ViewModel(
            $this->tonis->getViewManager()->getErrorTemplate(),
            [
                'exception' => $ex,
                'type' => 'invalid-dispatch-result',
                'path' => $request->getUri()->getPath()
            ]
        );
        $this->tonis->setDispatchResult($model);
    }

    /**
     * {@inheritDoc}
     */
    public function onDispatchException(Exception $ex)
    {
        $model = new ViewModel(
            $this->tonis->getViewManager()->getErrorTemplate(),
            [
                'exception' => $ex,
                'type' => 'exception'
            ]
        );
        $this->tonis->setDispatchResult($model);
    }

    /**
     * {@inheritDoc}
     */
    public function onRender()
    {
        $model = $this->tonis->getDispatchResult();

        if ($model instanceof ResponseInterface) {
            return;
        }

        if (!$model instanceof ViewModelInterface) {
            $this->tonis->setRenderResult(new InvalidViewModelException());
            return;
        }

        $model = $this->verifyModelTemplate($model);
        if (!$model instanceof ModelInterface) {
            return;
        }

        $this->tonis->setRenderResult($this->tonis->getViewManager()->render($model));
    }

    /**
     * {@inheritDoc}
     */
    public function onRenderException(Exception $ex)
    {
        $model = new ViewModel(
            $this->tonis->getViewManager()->getErrorTemplate(),
            [
                'exception' => $ex,
                'type' => 'render'
            ]
        );
        $this->tonis->setRenderResult($this->tonis->getViewManager()->render($model));
    }

    private function verifyModelTemplate(ModelInterface $model)
    {
        if ($model instanceof ViewModel && !$model->getTemplate()) {
            $match = $this->tonis->getRouteCollection()->getLastMatch();
            $handler = $match->getRoute()->getHandler();

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

                $model = new ViewModel($template, $model->getVariables());
            } else {
                $this->tonis->setRenderResult(new InvalidTemplateException('No template was available for rendering'));
                return null;
            }
        }

        return $model;
    }
}
