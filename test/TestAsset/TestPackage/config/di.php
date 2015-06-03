<?php

use Tonis\Di\Container;

return function (Container $di) {
    $di->set('foo', function() {
        return 'bar';
    });
};
