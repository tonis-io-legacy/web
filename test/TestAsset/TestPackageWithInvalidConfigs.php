<?php
namespace Tonis\Mvc\TestAsset;

use Tonis\Mvc\Package\AbstractPackage;

class TestPackageWithInvalidConfigs extends AbstractPackage
{
    public function getPath()
    {
        return __DIR__;
    }
}
