<p>
    A route matching <strong><?=$this->e($path)?></strong> does not exist. Verify that your configuration
    is correct and try again.
</p>
<pre>
// config/routes.php
use Tonis\Mvc\ViewModel;
use Tonis\Router\RouteCollection;

return function(RouteCollection $routes) {
    $routes->get('home', <?=$this->e($path)?>, function() {
        $model = new ViewModel;
        $model->setTemplate('my/action');

        return $model;
    });
}
</pre>
