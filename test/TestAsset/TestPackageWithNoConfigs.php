<?php
namespace Tonis\Mvc\TestAsset;

use Tonis\Mvc\Package\AbstractPackage;

class TestPackageWithNoConfigs extends AbstractPackage
{
    public function getPath()
    {
        return sys_get_temp_dir();
    }
}
