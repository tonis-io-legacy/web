<?php
namespace Tonis\Tonis\Integration;

use Tonis\Tonis\Factory\TonisFactory;
use Tonis\Tonis\TestAsset\TestPackage\TestPackage;
use Tonis\Tonis\Tonis;
use Tonis\View\Model\ViewModel;

class WebTest extends \PHPUnit_Framework_TestCase
{
    /** @var Tonis */
    private $tonis;

    public function testWebLoads()
    {
        $this->tonis->routes()->get('/', function () {
            return ['foo' => 'bar', '$$template' => '@test-package/test'];
        });

        $response = $this->tonis->__invoke();
        $body = (string) $response->getBody();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertNotEmpty($body);
        $this->assertContains('It worked!', $body);
    }

    public function testWebHandlesInvalidRoutes()
    {
        $response = $this->tonis->__invoke();
        $body = (string) $response->getBody();

        $this->assertSame(404, $response->getStatusCode());
        $this->assertNotEmpty($body);
        $this->assertContains('route not found', $body);
    }

    public function testWebHandlesDispatchExceptions()
    {
        $this->tonis->routes()->get('/', function () {
            throw new \RuntimeException('foobar');
        });

        $response = $this->tonis->__invoke();
        $body = (string) $response->getBody();

        $this->assertSame(500, $response->getStatusCode());
        $this->assertContains('application error', $body);
        $this->assertContains('An exception occurred during dispatching', $body);
        $this->assertContains('foobar', $body);
    }

    public function testWebHandlesInvalidDispatchResults()
    {
        $this->tonis->routes()->get('/', function () {
            return null;
        });

        $response = $this->tonis->__invoke();
        $body = (string) $response->getBody();

        $this->assertSame(500, $response->getStatusCode());
        $this->assertContains('application error', $body);
        $this->assertContains('An invalid result was returned from the dispatch action.', $body);
    }

    protected function setUp()
    {
        $this->tonis = (new TonisFactory)->createWeb(['packages' => TestPackage::class]);
    }
}
