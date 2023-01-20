<?php

declare(strict_types=1);

namespace BitrixRestApi\Controller;

use BitrixRestApi\Response\ResponseFacade;
use Psr\Container\ContainerInterface;

abstract class AbstractController
{
    protected ContainerInterface $container;
    protected ResponseFacade $response;

    function __construct(ContainerInterface $container, ResponseFacade $responseFacade)
    {
        $this->container = $container;
        $this->response = $responseFacade;
    }
}
