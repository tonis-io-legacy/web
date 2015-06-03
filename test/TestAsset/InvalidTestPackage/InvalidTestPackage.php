<?php
namespace Tonis\Mvc\TestAsset\InvalidTestPackage;

use Tonis\Mvc\Package\AbstractPackage;

class InvalidTestPackage extends AbstractPackage
{
    public function getPath()
    {
        return __DIR__;
    }
}
