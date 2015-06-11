<?php
namespace Tonis\Tonis;

/**
 * @coversDefaultClass \Tonis\Tonis\TonisConfig
 */
class TonisConfigTest extends \PHPUnit_Framework_TestCase
{
    /** @var TonisConfig */
    private $config;

    /**
     * @covers ::__construct
     * @covers ::isDebugEnabled
     */
    public function testIsDebugEnabled()
    {
        $this->assertFalse($this->config->isDebugEnabled());
    }

    /**
     * @covers ::getEnvironment
     */
    public function testGetEnvironment()
    {
        $this->assertEmpty($this->config->getEnvironment());
    }

    /**
     * @covers ::getSubscribers
     */
    public function testGetSubscribers()
    {
        $this->assertEmpty($this->config->getSubscribers());
    }

    /**
     * @covers ::getCacheDir
     */
    public function testGetCacheDir()
    {
        $this->assertSame(null, $this->config->getCacheDir());
    }

    /**
     * @covers ::getPackages
     */
    public function testGetPackages()
    {
        $this->assertEmpty($this->config->getPackages());
    }

    protected function setUp()
    {
        $this->config = new TonisConfig;
    }
}
