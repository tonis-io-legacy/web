<?php
namespace Tonis\Web\TestAsset;

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
        $vars = [];

        foreach ($model->getVariables() as $key => $variable) {
            if ($variable instanceof \Exception) {
                $variable = get_class($variable);
            }
            $vars[$key] = $variable;
        }

        return $model->getTemplate() . ':' . json_encode($vars);
    }
}
