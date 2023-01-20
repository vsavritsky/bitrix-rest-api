<?php

namespace App\Repository\Content;

use App\Entity\Content\News;

class NewsRepository extends \BitrixModels\Repository\ElementRepository
{
    protected $class = News::class;
}
