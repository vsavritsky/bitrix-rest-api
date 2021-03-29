<?php

namespace BitrixRestApi\Responser\Response;

use Swagger\Annotations as SWG;

class ApiNotFoundErrorResponse extends BaseErrorResponse implements \JsonSerializable
{
    public $errorCode = 'api_not_found';
    public $message = 'Апи не найдено';
    
    public function jsonSerialize()
    {
        return [
            'errorCode' => $this->errorCode,
            'message' => $this->message
        ];
    }
}
