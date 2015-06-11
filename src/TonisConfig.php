<?php

namespace Tonis\Tonis;

use Tonis\View\Strategy;

final class TonisConfig
{
    /** @var array */
    private $config;

    /**
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $defaults = [
            'debug' => false,
            'cache_dir' => null,
            'environment' => [],
            'required_environment' => ['TONIS_DEBUG'],
            'packages' => [],
            'subscribers' => [],
        ];

        $this->config = array_replace($defaults, $config);
    }

    /**
     * @return bool
     */
    public function isDebugEnabled()
    {
        return $this->config['debug'];
    }

    /**
     * @return string|null
     */
    public function getCacheDir()
    {
        return $this->config['cache_dir'];
    }

    /**
     * @return array
     */
    public function getEnvironment()
    {
        return $this->config['environment'];
    }

    /**
     * @return array
     */
    public function getPackages()
    {
        return $this->config['packages'];
    }

    /**
     * @return array
     */
    public function getSubscribers()
    {
        return $this->config['subscribers'];
    }
}
