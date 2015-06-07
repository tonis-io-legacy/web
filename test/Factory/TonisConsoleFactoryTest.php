<?php
namespace Tonis\Mvc\Factory;

use Tonis\Mvc\Tonis;
use Tonis\Mvc\TonisConsole;

/**
 * @coversDefaultClass \Tonis\Mvc\Factory\TonisConsoleFactory
 */
class TonisConsoleFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::fromDefaults
     */
    public function testInvoke()
    {
        $console = TonisConsoleFactory::fromDefaults();
        $tonis = $console->getTonis();
        $events = $tonis->events();

        $this->assertCount(2, $events->getListeners(Tonis::EVENT_BOOTSTRAP));
        $this->assertTrue($tonis->di()->has(TonisConsole::class));
    }
}
