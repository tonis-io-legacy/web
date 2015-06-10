<?php
namespace Tonis\Mvc\Integration;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Tonis\Mvc\Factory\TonisFactory;

class ConsoleTest extends \PHPUnit_Framework_TestCase
{
    public function testConsoleLoads()
    {
        $output = new BufferedOutput();

        $console = (new TonisFactory)->createConsole();
        $console->setAutoExit(false);
        $console->run(new ArrayInput([]), $output);

        $this->assertContains('Console Tool', $output->fetch());
    }
}
