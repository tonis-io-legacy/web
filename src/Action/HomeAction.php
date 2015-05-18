<?php
namespace Tonis\Mvc\Action;

use Tonis\View\ViewModel;

class HomeAction extends AbstractAction
{
    public function __invoke()
    {
        $model = new ViewModel();
        $model->setTemplate('tonis/index');

        return $model;
    }
}
