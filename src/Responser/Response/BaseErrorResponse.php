<?php

namespace BitrixRestApi\Responser\Response;

use Swagger\Annotations as SWG;

class BaseErrorResponse extends AbstractResponse implements \JsonSerializable
{
    public int $code = 400;

    /**
     * @var string
     * @SWG\Property(type="string", description="Код ошибки")
     */
    public string $errorCode = 'custom';

    /**
     * @var string
     * @SWG\Property(type="string", description="Текст ошибки")
     */
    public string $message = '';

    /**
     * @var string
     * @SWG\Property(type="string", description="Трейс ошибки")
     */
    public string $trace = '';

    /**
     * @return string
     */
    public function getTrace()
    {
        return $this->trace;
    }

    /** @return self */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @param string|array $trace
     */
    public function setTrace($trace)
    {
        if (is_array($trace)) {
            $this->trace = implode(', ', $trace);
        } else {
            $this->trace = $trace;
        }
    }

    public function setErrorCode(string $errorCode): BaseErrorResponse
    {
        $this->errorCode = $errorCode;

        return $this;
    }

    public function jsonSerialize()
    {
        return [
            'errorCode' => $this->errorCode,
            'message' => $this->message,
            'trace' => $this->trace,
            'code' => $this->code
        ];
    }
}
