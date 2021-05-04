<?php

namespace BitrixRestApi\Responser\Response;

class BaseSuccessResponse extends AbstractResponse implements \JsonSerializable
{
    public $code = 200;
    public $cacheId = null;
    public $mapper = null;
    
    /** @var PictureResizeService|null */
    protected $pictureResiseService = null;
    
    /** @var FileService|null */
    protected $fileService = null;
    
    public static $resultFields = [];
    
    public function __construct($object = null)
    {
        parent::__construct($object);
    }
    
    public function setCacheId($cacheId)
    {
        $this->cacheId = $cacheId;
    }
    
    public function jsonSerialize()
    {
        $result = [];
        
        foreach (static::$resultFields as $resultField) {
            $result[$resultField] = $this->$resultField;
        }
        
        $result['code'] = $this->code;
        $result['cacheId'] = $this->cacheId;
        
        return $result;
    }
}
