<?php

namespace App\Repository\Content;

use App\Entity\Content\Tag;

class TagRepository extends \BitrixModels\Repository\ElementRepository
{
    protected $class = Tag::class;
}
