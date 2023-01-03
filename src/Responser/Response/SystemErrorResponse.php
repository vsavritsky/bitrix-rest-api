<?php

namespace BitrixRestApi\Responser\Response;

class SystemErrorResponse extends BaseErrorResponse
{
    public string $errorCode = 'system_error';
    public string $message = 'Техническая ошибка';
}
