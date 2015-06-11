<?php
namespace Tonis\Web\TestAsset;

use Tonis\Di\Container;
use Tonis\Web\Factory\AbstractViewStrategyFactory;
use Tonis\Package\PackageManager;

class TestViewStrategyFactory extends AbstractViewStrategyFactory
{
    /**
     * @param Container $di
     * @return mixed
     */
    public function createService(Container $di)
    {
        return $this->getViewPaths($di->get(PackageManager::class));
    }
}
