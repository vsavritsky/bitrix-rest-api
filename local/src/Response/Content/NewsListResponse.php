<?php

namespace App\Response\Content;

use App\Entity\Content\News;
use BitrixModels\Model\Pagination;
use BitrixRestApi\Responser\Response\BaseSuccessResponse;

class NewsListResponse extends BaseSuccessResponse
{
    public static $resultFields = [
        'list',
        'pagination',
    ];

    protected array $list = [];
    protected array $pagination = [];

    /**
     * @param array $list
     */
    public function setList(array $list): void
    {
        $this->list = $list;

        $this->addCacheTag(News::class);
        $this->addCacheTagList($list);
    }

    /**
     * @param array $pagination
     */
    public function setPagination(Pagination $pagination): void
    {
        $this->pagination = $pagination->jsonSerialize();
    }
}
