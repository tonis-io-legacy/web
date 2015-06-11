<?php
namespace Tonis\Tonis\TestAsset;

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
