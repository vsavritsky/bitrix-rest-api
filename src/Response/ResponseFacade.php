<?php

namespace BitrixRestApi\Response;

use BitrixRestApi\Responser\Response\AbstractResponse;
use BitrixRestApi\Responser\Response\BaseSuccessResponse;
use Psr\Http\Message\ResponseInterface;

class ResponseFacade
{
    protected \Slim\Psr7\Response $response;

    public function __construct(\Slim\Psr7\Response $response)
    {
        $this->response = $response;
    }

    public function setContent(array | AbstractResponse $response = []): static
    {
        if (is_a($response, AbstractResponse::class)) {
            $data = $response->jsonSerialize();
        }

        $this->response
            ->getBody()
            ->write(json_encode($response));

        return $this;
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response->withStatus(200);
    }
}
