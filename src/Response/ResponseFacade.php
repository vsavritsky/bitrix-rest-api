<?php

namespace BitrixRestApi\Response;

use BitrixRestApi\Responser\Response\AbstractResponse;
use BitrixRestApi\Responser\Response\BaseSuccessResponse;
use Psr\Http\Message\ResponseInterface;

class ResponseFacade
{
    protected \Slim\Psr7\Response $response;

    protected AbstractResponse | array $dataResponse;

    public function __construct(\Slim\Psr7\Response $response)
    {
        $this->response = $response;
    }

    public function setContent($response = []): self
    {
        $this->dataResponse = $response;

        $this->response
            ->getBody()
            ->write(json_encode($response));

        return $this;
    }

    public function getResponse(): ResponseInterface
    {
        if (is_array($this->dataResponse) && $this->dataResponse['status']) {
            return $this->response->withStatus($this->dataResponse['status']);
        }

        if (is_object($this->dataResponse) && $this->dataResponse->code) {
            return $this->response->withStatus($this->dataResponse->code);
        }

        return $this->response->withStatus(200);
    }
}

