<?php
namespace Tonis\Mvc\TestAsset;

class TestTwigExtension extends \Twig_Extension
{
    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'test';
    }
}
