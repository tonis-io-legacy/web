<?php
namespace Tonis\Tonis;

use Tonis\Di\Container;
use Tonis\Tonis\Factory\TonisFactory;
use Tonis\Tonis\TestAsset\TestCommand;

/**
 * @coversDefaultClass \Tonis\Tonis\TonisConsole
 */
class TonisConsoleTest extends \PHPUnit_Framework_TestCase
{
    /** @var TonisConsole */
    private $console;

    /**
     * @covers ::__construct
     * @covers ::getTonis
     */
    public function testGetTonis()
    {
        $this->assertInstanceOf(Tonis::class, $this->console->getTonis());
    }

    /**
     * @covers ::add
     */
    public function testAdd()
    {
        $cmd = new TestCommand('Test');
        $this->console->add($cmd);

        $this->assertInstanceOf(Container::class, $cmd->di());
    }

    protected function setUp()
    {
        $this->console = (new TonisFactory)->createConsole([]);
    }
}
