<?php
namespace Tonis\Web\Integration;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Tonis\Web\AppFactory;

class ConsoleTest extends \PHPUnit_Framework_TestCase
{
    public function testConsoleLoads()
    {
        $output = new BufferedOutput();

        $console = (new AppFactory)->createConsole();
        $console->setAutoExit(false);
        $console->run(new ArrayInput([]), $output);

        $this->assertContains('Console Tool', $output->fetch());
    }
}
