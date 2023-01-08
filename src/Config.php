<?php

namespace BitrixRestApi;

use BitrixRestApi\Jwt\JwtManager;
use BitrixRestApi\Responser\ResponserInterface;

class Config
{
    protected $format;

    protected $authManager;

    public function setFormat(ResponserInterface $responser)
    {
        $this->format = $responser;
    }

    public function setAuthManager(JwtManager $manager)
    {
        $this->authManager = $manager;
    }
}
