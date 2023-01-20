<?php

namespace App\Response\Content;

use App\Entity\Content\Library;
use App\Entity\Content\Tag;
use BitrixModels\Model\Pagination;
use BitrixRestApi\Responser\Response\BaseSuccessResponse;

class LibraryListResponse extends BaseSuccessResponse
{
    public static $resultFields = [
        'list',
        'tags',
        'pagination',
    ];

    protected array $list = [];
    protected array $pagination = [];

    protected array $tags = [];

    /**
     * @param array $list
     */
    public function setList(array $list): void
    {
        $this->list = $list;

        $this->addCacheTag(Library::class);
        $this->addCacheTagList($list);
    }

    /**
     * @param array $pagination
     */
    public function setPagination(Pagination $pagination): void
    {
        $this->pagination = $pagination->jsonSerialize();
    }

    /**
     * @param array $tags
     */
    public function setTags(array $tags): void
    {
        $this->tags = $tags;

        $this->addCacheTag(Tag::class);
        $this->addCacheTagList($tags);
    }
}
