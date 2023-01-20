<?php

namespace App\Repository\User;

use App\Entity\User\Favorite;

class FavoriteRepository extends \BitrixModels\Repository\HighloadRepository
{
    protected $class = Favorite::class;

    public function __construct()
    {
        parent::__construct($this->class);
    }
}
