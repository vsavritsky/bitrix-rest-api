<?php

namespace BitrixRestApi;

use BitrixRestApi\Responser\Response\AbstractResponse;
use Symfony\Component\HttpFoundation\Request;

interface ApiInterface
{
    public function setRequest(Request $request);
    
    public function getRequest(): Request;
    
    public function setUser($user);
    
    public function getUser();
    
    public function response(AbstractResponse $response);
}
