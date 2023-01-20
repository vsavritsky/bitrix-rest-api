<?php

namespace App\Response\Content;

use App\Entity\Content\News;
use BitrixModels\Model\Pagination;
use BitrixRestApi\Responser\Response\BaseSuccessResponse;

class NewsViewResponse extends BaseSuccessResponse
{
    public static $resultFields = [
        'item',
    ];

    protected array $item = [];

    /**
     * @param array $list
     */
    public function setItem(array $item): void
    {
        $this->item = $item;

        $this->addCacheTag(News::class);
        $this->addCacheTag($item['id']);
    }
}
