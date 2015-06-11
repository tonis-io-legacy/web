Tonis features a ViewManager which enables you to use zero or more templating engines. For example, in APIs you can 
enable the JSON Strategy which will allow rendering JSON. For the web you can enable the PlatesStrategy for PlatesPHP
or the TwigStrategy for Twig. These are completely customizable and can be disabled at anytime.

View Model
----------

A view model is a model of what data the view layer has access to. You assign data to a model and then the view can interact 
with that data to render a result.

Tonis includes the following models as part of the `Tonis\View` component.

 * Tonis\View\Model\StringModel
 * Tonis\View\Model\JsonModel
 * Tonis\View\Model\PlatesModel
 * Tonis\View\Model\TwigModel 
 
View Strategy
-------------

A view strategy inspects the model at run-time to determine if it's capable of rendering and, if so, passes the model to 
the renderer to do work.

Tonis includes the following strategies as part of the `Tonis\View` component.

 * Tonis\View\Strategy\StringStrategy
 * Tonis\View\Strategy\JsonStrategy
 * Tonis\View\Strategy\PlatesStrategy
 * Tonis\View\Strategy\TwigStrategy

Template Names
--------------

Note: This section is only relevant for ViewModel's. If you are using Tonis for an API you can ignore this.

If you do not return a `Tonis\View\Model\ViewModel` then Tonis will attempt to guess a template name based on the
dispatchable name. This is very crud, however, and it is recommended that you set your template explicitly. When returning
an array you can do so by setting the `$$template` key. If no template is able to be determined then a
`Tonis\Web\Exception\InvalidTemplateException` will be thrown.
