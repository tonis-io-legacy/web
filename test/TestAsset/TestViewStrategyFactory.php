<?php
namespace Tonis\Web\TestAsset;

use Tonis\Di\Container;
use Tonis\Web\Factory\AbstractViewStrategyFactory;
use Tonis\Package\PackageManager;

class TestViewStrategyFactory extends AbstractViewStrategyFactory
{
    /**
     * @param Container $services
     * @return mixed
     */
    public function createService(Container $services)
    {
        return $this->getViewPaths($services->get(PackageManager::class));
    }
}
