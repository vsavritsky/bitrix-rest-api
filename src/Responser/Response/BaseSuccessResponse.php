<?php

namespace BitrixRestApi\Responser\Response;

class BaseSuccessResponse extends AbstractResponse implements \JsonSerializable
{
    public int $code = 200;
    public string|null $cacheId = null;

    public array $tags = [];

    public static $resultFields = [];

    public function __construct($object = null)
    {
        parent::__construct($object);
    }

    public function setCacheId($cacheId)
    {
        $this->cacheId = $cacheId;
    }

    /**
     * @return array
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    public function addTag($tag)
    {
        $this->tags[] = $tag;
    }

    public function addTagList($list)
    {
        foreach ($list as $item) {
            if (is_object($item)) {
                $this->addTag($item->getId());
            } elseif (is_array($item) && isset($item['ID'])) {
                $this->addTag($item['ID']);
            }
        }
    }

    /**
     * @param array $tags
     */
    public function setTags(array $tags): void
    {
        $this->tags = $tags;
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
