<?php

namespace BitrixRestApi\Responser\Response;

use Service\Api\Mapper\ApiMapperInterface;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;

class AbstractResponse
{
    const STATUS_SUCCESS = 200;
    const STATUS_PUT = 201;

    /**
     * @var integer
     * @SWG\Property(type="integer", description="Код ответа")
     */
    public $code;

    public function __construct($object = null)
    {
        if ($object) {
            $this->populate($object);
        }
    }

    public function populate($object)
    {

    }
}
