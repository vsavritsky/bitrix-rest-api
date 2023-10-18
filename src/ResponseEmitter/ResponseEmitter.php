<?php

declare(strict_types=1);

namespace BitrixRestApi\ResponseEmitter;

use Psr\Http\Message\ResponseInterface;
use Slim\ResponseEmitter as SlimResponseEmitter;
use Bitrix\Main\Config\Option;

class ResponseEmitter extends SlimResponseEmitter
{
    /**
     * {@inheritdoc}
     */
    public function emit(ResponseInterface $response): void
    {
        $protocol = $_SERVER['PROTOCOL'] = (!empty($_SERVER['HTTPS']) || $_SERVER["SERVER_PORT"] == 443) ? 'https' : 'http';
        $origin = $_SERVER['HTTP_HOST'];

        $accessControlAllowOrigin = Option::get('site.settings', 'rest.access-control-allow-origin', sprintf('%s://%s', $protocol, $origin));

        $response = $response
            ->withHeader('Access-Control-Allow-Credentials', 'true')
            ->withHeader('Access-Control-Allow-Origin', $accessControlAllowOrigin)
            ->withHeader(
                'Access-Control-Allow-Headers',
                'X-Requested-With, Content-Type, Accept, Origin, Authorization',
            )
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
            ->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->withAddedHeader('Cache-Control', 'post-check=0, pre-check=0')
            ->withHeader('Pragma', 'no-cache');

        if (ob_get_contents()) {
            ob_clean();
        }

        parent::emit($response);
    }
}
