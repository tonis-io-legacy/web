<?php
namespace Tonis\Web\TestAsset\InvalidTestPackage;

use Tonis\Web\Package\AbstractPackage;

class InvalidTestPackage extends AbstractPackage
{
    public function getPath()
    {
        return __DIR__;
    }
}
