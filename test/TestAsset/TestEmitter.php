<?php
namespace Tonis\Tonis\TestAsset;

use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response\EmitterInterface;

class TestEmitter implements EmitterInterface
{
    /** @var ResponseInterface */
    private $response;

    /**
     * {@inheritDoc}
     */
    public function emit(ResponseInterface $response)
    {
        ob_end_flush();

        $this->response = $response;
    }

    /**
     * @return ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }
}
