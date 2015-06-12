<?php

namespace Tonis\Web;

use Tonis\View\Strategy;

final class AppConfig
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
        return (bool) $this->config['debug'];
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
        return (array) $this->config['environment'];
    }

    /**
     * @return array
     */
    public function getRequiredEnvironment()
    {
        return (array) $this->config['required_environment'];
    }

    /**
     * @return array
     */
    public function getPackages()
    {
        return (array) $this->config['packages'];
    }

    /**
     * @return array
     */
    public function getSubscribers()
    {
        return (array) $this->config['subscribers'];
    }
}
