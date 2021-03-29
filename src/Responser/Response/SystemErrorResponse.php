<?php

namespace BitrixRestApi\Responser\Response;

class SystemErrorResponse extends BaseErrorResponse
{
    public $errorCode = 'system_error';
    public $message = 'Техническая ошибка';
}
