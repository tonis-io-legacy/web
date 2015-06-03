<?php
namespace Tonis\Mvc\TestAsset\TestPackage;

use Tonis\Mvc\Package\AbstractPackage;

class TestPackage extends AbstractPackage
{
    public function getPath()
    {
        return __DIR__;
    }
}
