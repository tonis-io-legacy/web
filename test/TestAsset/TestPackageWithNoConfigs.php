<?php
namespace Tonis\Tonis\TestAsset;

use Tonis\Tonis\Package\AbstractPackage;

class TestPackageWithNoConfigs extends AbstractPackage
{
    public function getPath()
    {
        return sys_get_temp_dir();
    }
}
