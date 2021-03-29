<?php

namespace BitrixRestApi\Responser\Response;

use Service\PictureResizeService;

class BaseSuccessResponse extends AbstractResponse implements \JsonSerializable
{
    public $code = 200;
    public $cacheId = null;
    
    /** @var PictureResizeService|null  */
    protected $pictureResiseService = null;
    
    public function __construct($object = null)
    {
        parent::__construct($object);
    
        $this->pictureResiseService = new PictureResizeService();
    }
    
    public function setCacheId($cacheId)
    {
        $this->cacheId = $cacheId;
    }
    
    public function jsonSerialize()
    {
        return [
            'cacheId' => $this->cacheId
        ];
    }
}
