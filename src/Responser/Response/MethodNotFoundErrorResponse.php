<?php

namespace BitrixRestApi\Responser\Response;

use Swagger\Annotations as SWG;

class MethodNotFoundErrorResponse extends BaseErrorResponse implements \JsonSerializable
{
    public string $errorCode = 'method_not_found';

    /**
     * @var string
     * @SWG\Property(type="string", description="Текст ошибки")
     */
    public string $message = 'Метод не найден';

    public function jsonSerialize()
    {
        return [
            'errorCode' => $this->errorCode,
            'message' => $this->message
        ];
    }
}
