<?php
namespace Tonis\Mvc;

use Tonis\Di\Container;
use Tonis\Mvc\Factory\TonisConsoleFactory;
use Tonis\Mvc\TestAsset\TestCommand;

/**
 * @coversDefaultClass \Tonis\Mvc\TonisConsole
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
        $this->console = TonisConsoleFactory::fromDefaults();
    }
}
