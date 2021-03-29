<?php

namespace BitrixRestApi\Responser\Response;

use Swagger\Annotations as SWG;

class BaseErrorResponse extends AbstractResponse implements \JsonSerializable
{
    public $code = 400;
    
    /**
     * @var string
     * @SWG\Property(type="string", description="Код ошибки")
     */
    public $errorCode = 'custom';
    
    /**
     * @var string
     * @SWG\Property(type="string", description="Текст ошибки")
     */
    public $message = '';
    
    /**
     * @var string
     * @SWG\Property(type="string", description="Трейс ошибки")
     */
    public $trace = '';
    
    /**
     * @return string
     */
    public function getTrace()
    {
        return $this->trace;
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
    
    public function jsonSerialize()
    {
        return [
            'errorCode' => $this->errorCode,
            'message' => $this->message,
            'trace' => $this->trace
        ];
    }
}
