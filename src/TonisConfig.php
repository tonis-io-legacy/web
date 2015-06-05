<?php

namespace Tonis\Mvc;

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
            'environment' => [],
            'packages' => [],
        ];

        $this->config = array_replace_recursive($defaults, $config);
    }

    /**
     * @return bool
     */
    public function isDebugEnabled()
    {
        return $this->config['debug'];
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
}
