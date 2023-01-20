<?php

namespace App\Response\Catalog;

use App\Entity\Catalog\Product;
use BitrixModels\Model\Pagination;
use BitrixRestApi\Responser\Response\BaseSuccessResponse;

class ProductViewResponse extends BaseSuccessResponse
{
    public static $resultFields = [
        'item',
    ];

    protected array $item = [];

    /**
     * @param array $item
     */
    public function setItem(array $item): void
    {
        $this->item = $item;
    }
}
