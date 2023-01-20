<?php

namespace App\Response\Content;

use App\Entity\Content\Library;
use BitrixRestApi\Responser\Response\BaseSuccessResponse;

class LibraryViewResponse extends BaseSuccessResponse
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

        $this->addCacheTag(Library::class);
        $this->addCacheTag($item['id']);
    }
}
