<?php
namespace Tonis\Web\TestAsset;

use Tonis\Web\Package\AbstractPackage;

class TestPackageWithNoConfigs extends AbstractPackage
{
    public function getPath()
    {
        return sys_get_temp_dir();
    }
}
