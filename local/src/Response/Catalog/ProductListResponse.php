<?php

namespace App\Response\Catalog;

use App\Entity\Catalog\Product;
use BitrixModels\Model\Pagination;
use BitrixRestApi\Responser\Response\BaseSuccessResponse;

class ProductListResponse extends BaseSuccessResponse
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

        $this->addCacheTag(Product::class);
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
