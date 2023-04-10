<?php

namespace BitrixRestApi\Responser\Response;

class BaseSuccessResponse extends AbstractResponse implements \JsonSerializable
{
    public int $code = 200;
    public string $cacheId = '';

    public array $cacheTags = [];

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
    public function getCacheTags(): array
    {
        return $this->cacheTags;
    }

    public function addCacheTag($tag)
    {
        $this->cacheTags[] = $tag;
    }

    public function addCacheTagList($list)
    {
        foreach ($list as $item) {
            if (is_object($item)) {
                $this->addCacheTag($item->getId());
            } elseif (is_array($item) && isset($item['id'])) {
                $this->addCacheTag((int)$item['id']);
            }
        }
    }

    /**
     * @param array $tags
     */
    public function setCacheTags(array $tags): void
    {
        $this->cacheTags = $tags;
    }

    public function jsonSerialize()
    {
        $result = [];

        foreach (static::$resultFields as $resultField) {
            $result[$resultField] = $this->$resultField;
        }

        $result['code'] = $this->code;
        $result['cacheId'] = $this->cacheId;
        $result['cacheTags'] = $this->getCacheTags();

        return $result;
    }
}
