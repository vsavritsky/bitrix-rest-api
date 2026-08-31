<?php

namespace BitrixRestApi\Middleware;

use BitrixRestApiCache\Cache\CacheManager;
use BitrixRestApiCache\Cache\PhpCache;

class CacheMiddleware
{
    public function __construct(
        private int $cacheTime = CacheManager::DEFAULT_CACHE_TIME,
    ) {
    }

    public function withCacheTime(int $cacheTime): self
    {
        $clone = clone $this;
        $clone->cacheTime = $cacheTime;

        return $clone;
    }

    public function __invoke($request, $handler)
    {
        if ($request->getMethod() == 'GET') {
            $cache = new PhpCache($request);
            $result = $cache->init($this->cacheTime);
            if (!$result) {
                /** @var \Slim\Psr7\Response $response */
                $response = $handler->handle($request);
                $body = json_decode((string) $response->getBody(), true);
                $cache->cache($body);
            } else {
                $response = new \Slim\Psr7\Response();
                $body = $response->getBody();
                $body->write(json_encode($result, JSON_HEX_QUOT | JSON_HEX_TAG));
                $response->withBody($body);
            }
        }

        return $response;
    }
}
