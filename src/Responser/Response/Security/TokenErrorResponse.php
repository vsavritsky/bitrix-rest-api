<?php

namespace BitrixRestApi\Responser\Response\Security;

use BitrixRestApi\Responser\Response\BaseErrorResponse;
use Swagger\Annotations as SWG;

class TokenErrorResponse extends BaseErrorResponse
{
    public int $code = 400;
    public string $errorCode = 'token_not_valid';
    public string $message = 'Токен авторизации не валиден';
}
