<?php
namespace Tonis\Web\Integration;

use Tonis\Web\App;
use Tonis\Web\AppFactory;
use Tonis\Web\TestAsset\TestPackage\TestPackage;
use Tonis\View\Model\ViewModel;

class WebTest extends \PHPUnit_Framework_TestCase
{
    /** @var App */
    private $app;

    public function testWebLoads()
    {
        $this->app->getRouter()->get('/', function () {
            return ['foo' => 'bar', '$$template' => '@test-package/test'];
        });

        $response = $this->app->__invoke();
        $body = (string) $response->getBody();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertNotEmpty($body);
        $this->assertContains('It worked!', $body);
    }

    public function testWebHandlesInvalidRoutes()
    {
        $response = $this->app->__invoke();
        $body = (string) $response->getBody();

        $this->assertSame(404, $response->getStatusCode());
        $this->assertNotEmpty($body);
        $this->assertContains('route not found', $body);
    }

    public function testWebHandlesDispatchExceptions()
    {
        $this->app->getRouter()->get('/', function () {
            throw new \RuntimeException('foobar');
        });

        $response = $this->app->__invoke();
        $body = (string) $response->getBody();

        $this->assertSame(500, $response->getStatusCode());
        $this->assertContains('application error', $body);
        $this->assertContains('An exception occurred during dispatching', $body);
        $this->assertContains('foobar', $body);
    }

    public function testWebHandlesInvalidDispatchResults()
    {
        $this->app->getRouter()->get('/', function () {
            return null;
        });

        $response = $this->app->__invoke();
        $body = (string) $response->getBody();

        $this->assertSame(500, $response->getStatusCode());
        $this->assertContains('application error', $body);
        $this->assertContains('An invalid result was returned from the dispatch action.', $body);
    }

    protected function setUp()
    {
        $this->app = (new AppFactory)->createWeb(['packages' => TestPackage::class, 'debug' => true]);
    }
}
