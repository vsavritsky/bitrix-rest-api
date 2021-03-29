<?php

namespace BitrixRestApi\Responser\Response\Security;

use BitrixRestApi\Responser\Response\BaseErrorResponse;
use Swagger\Annotations as SWG;

class TokenErrorResponse extends BaseErrorResponse
{
    public $code = 400;
    public $errorCode = 'token_not_valid';
    public $message = 'Токен авторизации не валиден';
}
