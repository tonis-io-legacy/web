<?php
namespace Tonis\Mvc\TestAsset;

use Tonis\View\Model\ViewModel;
use Tonis\View\ModelInterface;
use Tonis\View\StrategyInterface;

class TestViewModelStrategy implements StrategyInterface
{
    /**
     * {@inheritDoc}
     */
    public function canRender(ModelInterface $model)
    {
        return $model instanceof ViewModel;
    }

    /**
     * {@inheritDoc}
     */
    public function render(ModelInterface $model)
    {
        if (!$model instanceof ViewModel) {
            return '';
        }
        return $model->getTemplate();
    }
}
