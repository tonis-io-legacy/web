<?php
use Tonis\Di\Container;
use Tonis\View\Twig\TwigResolver;
use Tonis\View\Twig\TwigStrategy;
use Tonis\View\Twig\TwigEnvironmentFactory;
use Tonis\View\Twig\TwigRenderer;

return function(Container $di) {
    $di->set('Twig', function($di) {
        return (new TwigEnvironmentFactory())->createService($di['tonis']['twig']);
    });

    $di->set('Tonis\View\Twig\TwigResolver', function(Container $di) {
        return new TwigResolver($di->get('Twig'));
    });

    $di->set('Tonis\View\Twig\TwigRenderer', function(Container $di) {
        return new TwigRenderer($di->get('Twig'), $di->get('Tonis\View\Twig\TwigResolver'));
    });

    $di->set('Tonis\View\Twig\TwigStrategy', function(Container $di) {
        return new TwigStrategy($di->get('Tonis\View\Twig\TwigRenderer'), $di->get('Tonis\View\Twig\TwigResolver'));
    });
};
