<?php
namespace Tonis\Web\TestAsset;

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
