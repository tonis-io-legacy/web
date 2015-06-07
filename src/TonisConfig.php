<?php

namespace Tonis\Mvc;

use Tonis\Mvc\Subscriber\BootstrapSubscriber;
use Tonis\Mvc\Subscriber\HttpSubscriber;
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
            'services' => [],
            'subscribers' => [
                BootstrapSubscriber::class => function ($di) {
                    return new BootstrapSubscriber($di);
                },
                HttpSubscriber::class => function ($di) {
                    return new HttpSubscriber($di);
                }
            ],
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
