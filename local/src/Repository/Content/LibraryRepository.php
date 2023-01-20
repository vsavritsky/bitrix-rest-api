<?php

namespace App\Repository\Content;

use App\Entity\Content\Library;

class LibraryRepository extends \BitrixModels\Repository\ElementRepository
{
    protected $class = Library::class;
}
