<?php
namespace Tonis\Tonis\TestAsset\InvalidTestPackage;

use Tonis\Tonis\Package\AbstractPackage;

class InvalidTestPackage extends AbstractPackage
{
    public function getPath()
    {
        return __DIR__;
    }
}
