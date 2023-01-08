<?php

namespace BitrixRestApi;

use BitrixModels\Entity\BaseModel;
use BitrixRestApi\Responser\Response\AbstractResponse;
use Symfony\Component\HttpFoundation\Request;

interface ApiInterface
{
    public function setRequest(Request $request);

    public function getRequest(): Request;

    public function setUser(BaseModel $user = null);

    public function getUser(): BaseModel|null;

    public function response(AbstractResponse $response);
}
