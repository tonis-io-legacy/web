<?php
namespace Tonis\Web;

use Tonis\Di\Container;
use Tonis\Web\TestAsset\TestCommand;

/**
 * @coversDefaultClass \Tonis\Web\Console
 */
class ConsoleTest extends \PHPUnit_Framework_TestCase
{
    /** @var Console */
    private $console;

    /**
     * @covers ::__construct
     * @covers ::getApp
     */
    public function testGetTonis()
    {
        $this->assertInstanceOf(App::class, $this->console->getApp());
    }

    /**
     * @covers ::add
     */
    public function testAdd()
    {
        $cmd = new TestCommand('Test');
        $this->console->add($cmd);

        $this->assertInstanceOf(Container::class, $cmd->getServiceContainer());
    }

    protected function setUp()
    {
        $this->console = (new AppFactory)->createConsole([]);
    }
}
